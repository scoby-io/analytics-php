<?php namespace Scoby\Analytics;

use Exception;
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
    private string $apiEndpoint;

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
     * @var string
     */
    private string $referringUrl;

    /**
     * @var array
     */
    private array $options = [
        'collectIpAddress' => false,
        'generateVisitorId' => true,
    ];

    /**
     * @var LoggerInterface
     */
    private ?LoggerInterface $logger = null;

    /**
     * @param string $jarId
     * @throws Exception
     */
    public function __construct(string $jarId)
    {
        if (empty($jarId)) {
            throw new Exception('Cannot initialize scoby analytics without $jarId.');
        }
        $this->jarId = $jarId;
        $this->apiEndpoint = "https://" . $this->jarId . ".s3y.io/count";

        $this->ipAddress = Helpers::getIpAddress();
        $this->userAgent = Helpers::getUserAgent();
        $this->requestedUrl = Helpers::getRequestedUrl();
        $this->referringUrl = Helpers::getReferringUrl();
    }

    /**
     * @param bool $collectIpAddress
     * @return $this
     */
    public function collectIpAddress(bool $collectIpAddress): Client
    {
        $this->options['collectIpAddress'] = $collectIpAddress;
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
        $this->visitorId = hash('sha256', implode("|", [$visitorId, $this->jarId]));
        $this->generateVisitorId(false);
        return $this;
    }

    /**
     * @return void
     */
    private function maybeUpdateVisitorId(): void
    {
        if ($this->options['generateVisitorId']) {
            $this->visitorId = hash('sha256', implode("|", [$this->ipAddress, $this->userAgent, $this->jarId]));
        }
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
            "ref" => $this->referringUrl,
            "ua" => $this->userAgent,
        ];
        if ($this->options['collectIpAddress']) {
            $params['ip'] = $this->ipAddress;
        }
        return $this->apiEndpoint . "?" . http_build_query($params);
    }

    public function logPageView(): void
    {
        try {
            $url = $this->getUrl();
            $this->logger?->debug("calling url: " . $url);
            $context = stream_context_create([
                "Http" => [
                    "timeout" => 5,
                ],
            ]);
            $headers = get_headers($url, true, $context);
            $statusCode = intval(substr($headers[0], 9, 3));
            if ($statusCode >= 400) {
                $this->logger?->error(
                    "scoby - failed logging page view (" . $statusCode . "): " . $url
                );
            } else {
                $this->logger?->info(
                    "scoby - successfully logged page view (" . $statusCode . "): " . $url
                );
            }
        } catch (Exception $exception) {
            $this->logger?->error(
                "scoby - failed logging page view: " . $exception->getMessage()
            );
        }
    }

    public function logPageViewAsync(): void
    {
        $that = $this;
        register_shutdown_function(function () use ($that) {
            $that->logPageView();
        });
    }
}
