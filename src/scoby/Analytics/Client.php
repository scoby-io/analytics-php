<?php namespace Scoby\Analytics;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use http\Url;
use Psr\Log\LoggerInterface;

class Client
{
    /**
     * @var string
     */
    private string $workspaceId;

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
     * @var string[]
     */
    private array $visitorSegments = [];

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
        'ipBlackList' => [],
        'whitelistedQueryParameters' => ['utm_medium', 'utm_source', 'utm_campaign', 'utm_content', 'utm_term']
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
        $this->workspaceId = $this->getWorkspaceId();
        $this->apiHost = "https://" . $this->workspaceId . ".s3y.io";
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
     * @param string $range
     * @return $this
     */
    public function blacklistIpRange(string $range): Client
    {
        $this->options['ipBlackList'][] = \IPLib\Factory::parseRangeString($range);
        return $this;
    }

    /**
     * @param string $pattern
     * @return $this
     */
    public function whitelistQueryParameter(string $key): Client
    {
        $this->options['whitelistedQueryParameters'][] = $key;
        return $this;
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
        $this->visitorId = hash_hmac('sha256', implode("|", [$visitorId, $this->workspaceId]), $this->salt);
        $this->generateVisitorId(false);
        return $this;
    }

    /**
     * @param string $segment
     * @return Client
     */
    public function addVisitorToSegment(string $segment): Client
    {
        $this->visitorSegments[] = $segment;
        return $this;
    }

    /**
     * @return void
     */
    private function maybeUpdateVisitorId(): void
    {
        if ($this->options['generateVisitorId']) {
            $this->visitorId = hash_hmac('sha256', implode("|", [$this->ipAddress, $this->userAgent, $this->workspaceId]), $this->salt);
        }
    }

    /**
     * @return string
     */
    private function getWorkspaceId(): string
    {
        $parts = explode("|", base64_decode($this->apiKey));
        return $parts[0];
    }

    /**
     * @return bool
     */
    private function isBlockedIp(): bool
    {
        try {
            if (!empty($this->options['ipBlackList'])) {
                $address = \IPLib\Factory::parseAddressString($this->ipAddress);
                foreach ($this->options['ipBlackList'] as $range) {
                    if ($range->contains($address)) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            if ($this->logger) $this->logger->warning(
                "scoby - IP blacklist could not be applied: " . $e->getMessage()
            );
        }

        return false;
    }

    private function getRequestedUrl(): string
    {
        $urlObj = \Spatie\Url\Url::fromString($this->requestedUrl);

        $queryParamsBag = $urlObj->getAllQueryParameters();

        foreach ($queryParamsBag as $key => $value) {
            if (!in_array($key, $this->options['whitelistedQueryParameters'])) {
                $urlObj = $urlObj->withoutQueryParameter($key);
            }
        }

        return (string)$urlObj;
    }

    private function getReferringUrl(): string
    {
        $urlObj = \Spatie\Url\Url::fromString($this->referringUrl);

        $scheme = $urlObj->getScheme();
        $host = $urlObj->getHost();

        return (string) \Spatie\Url\Url::create()
            ->withScheme($scheme)
            ->withHost($host);
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

    /**
     * @return string
     */
    public function getUrl(): string
    {
        $this->maybeUpdateVisitorId();
        $params = [
            "vid" => $this->visitorId,
            "url" => $this->getRequestedUrl(),
            "ua" => $this->userAgent,
        ];
        if ($this->referringUrl) {
            $params['ref'] = $this->getReferringUrl();
        }
        if ($this->visitorSegments) {
            $params['sg'] = implode(",", $this->visitorSegments);
        }
        return $this->apiHost . "/count?" . http_build_query($params);
    }

    /**
     * @return bool
     */
    public function logPageView(): bool
    {
        try {
            if ($this->isBlockedIp()) {
                if ($this->logger) $this->logger->info(
                    "scoby - skipped logging page view for blocked IP address."
                );
                return false;
            }

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

    /**
     * @return void
     */
    public function logPageViewAsync(): void
    {
        $that = $this;
        register_shutdown_function(function () use ($that) {
            $that->logPageView();
        });
    }

    /**
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            return $this->getApiStatus()->getStatusCode() === 200;
        } catch (GuzzleException $exception) {
            return false;
        }
    }

    /**
     * @return Response
     * @throws GuzzleException
     */
    public function getApiStatus(): Response
    {
        return $this->httpClient->request('GET', $this->apiHost . "/status", $this->requestOptions);
    }
}
