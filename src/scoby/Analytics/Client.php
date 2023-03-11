<?php namespace Scoby\Analytics;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * @var string
     */
    private string $jarId;

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @var string
     */
    private string $salt;

    /**
     * @var string
     */
    private string $apiHost;

    /**
     * @var string
     */
    private string $userAgent;

    /**
     * @var string
     */
    private string $visitorId;

    /**
     * @var string
     */
    private string $ipAddress;

    /**
     * @var string
     */
    private string $requestedUrl;

    /**
     * @var ?string
     */
    private ?string $referringUrl;

    /**
     * @var array
     */
    private array $options = [
        'generateVisitorId' => true,
    ];

    private array $requestOptions = [];

    /**
     * @var LoggerInterface
     */
    private ?LoggerInterface $logger = null;

    /**
     * @var HttpClient
     */
    private HttpClient $httpClient;

    /**
     * @param string $apiKey
     * @param string $salt
     * @throws Exception
     */
    public function __construct(string $apiKey, string $salt)
    {
        if (empty($apiKey)) {
            throw new Exception('Cannot initialize scoby analytics without $apiKey.');
        }

        if (empty($salt)) {
            throw new Exception('Cannot initialize scoby analytics without $salt.');
        }

        $this->apiKey = $apiKey;
        $this->salt = $salt;
        $this->jarId = $this->getJarId();
        $this->apiHost = "https://" . $this->jarId . ".s3y.io";
        $this->requestOptions = [
            'timeout' => 5,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey
            ]
        ];

        $this->ipAddress = Helpers::getIpAddress();
        $this->userAgent = Helpers::getUserAgent();
        $this->requestedUrl = Helpers::getRequestedUrl();
        $this->referringUrl = Helpers::getReferringUrl();

        $this->httpClient = new HttpClient();
    }

    /**
     * @param bool $generateVisitorId
     * @return $this
     */
    public function generateVisitorId(bool $generateVisitorId): Client
    {
        $this->options['generateVisitorId'] = $generateVisitorId;
        return $this;
    }

    /**
     * Override the automatically generated visitorId hash
     *
     * This value serves as the basis for your "unique visitors" metric
     * and may be useful if you want to e.g. count your logged-in users.
     *
     * @param string $visitorId
     * @return Client
     */
    public function setVisitorId(string $visitorId): Client
    {
        $this->visitorId = hash_hmac('sha256', implode("|", [$visitorId, $this->jarId]), $this->salt);
        $this->generateVisitorId(false);
        return $this;
    }

    /**
     * @return void
     */
    private function maybeUpdateVisitorId(): void
    {
        if ($this->options['generateVisitorId']) {
            $this->visitorId = hash_hmac('sha256', implode("|", [$this->ipAddress, $this->userAgent, $this->jarId]), $this->salt);
        }
    }

    private function getJarId(): string {
        $parts = explode("|", base64_decode($this->apiKey));
        return $parts[0];
    }

    /**
     * @param string $ipAddress
     * @return Client
     */
    public function setIpAddress(string $ipAddress): Client
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @param string $userAgent
     * @return Client
     */
    public function setUserAgent(string $userAgent): Client
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /**
     * @param string $requestedUrl
     * @return Client
     */
    public function setRequestedUrl(string $requestedUrl): Client
    {
        $this->requestedUrl = $requestedUrl;
        return $this;
    }

    /**
     * @param string $referringUrl
     * @return Client
     */
    public function setReferringUrl(string $referringUrl): Client
    {
        $this->referringUrl = $referringUrl;
        return $this;
    }

    /**
     * @param LoggerInterface $logger
     * @return Client
     */
    public function setLogger(LoggerInterface $logger): Client
    {
        $this->logger = $logger;
        return $this;
    }

    public function getUrl(): string
    {
        $this->maybeUpdateVisitorId();
        $params = [
            "vid" => $this->visitorId,
            "url" => $this->requestedUrl,
            "ua" => $this->userAgent,
        ];
        if ($this->referringUrl) {
            $params['ref'] = $this->referringUrl;
        }
        return $this->apiHost . "/count?" . http_build_query($params);
    }

    public function logPageView(): bool
    {
        try {
            $url = $this->getUrl();
            if ($this->logger) $this->logger->debug("calling url: " . $url);

            $res = $this->httpClient->request('GET', $url, $this->requestOptions);
            $statusCode = $res->getStatusCode();
            if ($statusCode === 204) {
                if ($this->logger) $this->logger->info(
                    "scoby - successfully logged page view (" . $statusCode . "): " . $url
                );
                return true;
            } else {
                if ($this->logger) $this->logger->error(
                    "scoby - failed logging page view (" . $statusCode . "): " . $url
                );
            }
        } catch (Exception|GuzzleException $exception) {
            if ($this->logger) $this->logger->error(
                "scoby - failed logging page view: " . $exception->getMessage()
            );
        }

        return false;
    }

    public function logPageViewAsync(): void
    {
        $that = $this;
        register_shutdown_function(function () use ($that) {
            $that->logPageView();
        });
    }

    public function testConnection(): bool
    {
        try {
            return $this->getApiStatus()->getStatusCode() === 200;
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    public function getApiStatus(): Response
    {
        return $this->httpClient->request('GET', $this->apiHost . "/status", $this->requestOptions);
    }
}
