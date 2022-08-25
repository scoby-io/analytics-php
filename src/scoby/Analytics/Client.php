<?php namespace Scoby\Analytics;

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
     * @var LoggerInterface
     */
    private ?LoggerInterface $logger = null;

    /**
     * @param string $jarId
     * @throws \Exception
     */
    public function __construct(string $jarId)
    {
        if (empty($jarId)) {
            throw new \Exception('Cannot initialize scoby analytics without $jarId.');
        }
        $this->jarId = $jarId;
        $this->apiEndpoint = "https://" . $this->jarId . ".s3y.io/count";
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

    private function gatherRequestData(): array
    {
        $userAgent = $this->userAgent ?: $_SERVER["HTTP_USER_AGENT"];
        $ipAddress = $this->ipAddress ?: $_SERVER["REMOTE_ADDR"];
        $requestedUrl =
            $this->requestedUrl ?:
                (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
                    ? "https"
                    : "Http") .
                "://" .
                $_SERVER["HTTP_HOST"] .
                $_SERVER["REQUEST_URI"];
        $referringUrl =
            $this->referringUrl ?:
                (!empty($_SERVER["HTTP_REFERER"])
                    ? $_SERVER["HTTP_REFERER"]
                    : null);

        return [
            "ip" => $ipAddress,
            "url" => $requestedUrl,
            "ref" => $referringUrl,
            "ua" => $userAgent,
        ];
    }

    public function getUrl(): string
    {
        $params = $this->gatherRequestData();
        return $this->apiEndpoint . "?" . http_build_query($params);
    }

    public function logPageView(): void
    {
        $url = $this->getUrl();
        $this->logger?->info("calling url: " . $url);
        $context = stream_context_create([
            "Http" => [
                "timeout" => 5,
            ],
        ]);
        try {
            $headers = get_headers($url, true, $context);
            $statusCode = intval(substr($headers[0], 9, 3));
            if ($statusCode >= 400) {
                $this->logger?->error(
                    "scoby - failed calling url (" . $statusCode . "): " . $url
                );
            } else {
                $this->logger?->debug(
                    "scoby - successfully called url (" . $statusCode . "): " . $url
                );
            }
        } catch (\Exception $exception) {
            $this->logger?->error(
                "scoby - failed calling url: " . $exception->getMessage()
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
