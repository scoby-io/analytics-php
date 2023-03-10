<?php

use PHPUnit\Framework\TestCase;
use Scoby\Analytics\Client;
use Spatie\Snapshots\MatchesSnapshots;

class ClientTest extends TestCase
{
    use MatchesSnapshots;

    public function setUp(): void
    {
        $_SERVER["HTTP_USER_AGENT"] = "Mozilla/Safari/Chrome/1.2";
        $_SERVER["REMOTE_ADDR"] = "2.3.4.5";
        $_SERVER["HTTP_REFERER"] = "https://www.referrer.de/i-refer?bar=foo";
        $_SERVER["HTTPS"] = true;
        $_SERVER["HTTP_HOST"] = "www.this-host-was-called.com";
        $_SERVER["REQUEST_URI"] = "/and-this-was-the-path?with=query";
    }

    public function testManualSettingOfParams()
    {
        $Client = new Client("YWJjMTIzfFE0YWt5MEhXb0t4N28xdXRCRnlRY2x5WnBRdTJZR0J1", "m7cQPvnI8CqwrSDfA1Ynrg==");
        $url = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->getUrl();

        $this->assertMatchesSnapshot($url);
    }

    public function testAutomaticCollection()
    {
        $client = new Client("eHl6Nzg5fDhRZWJ5emlYc2lxVUdlSHpYU0R6YWpQaFVVS2R5czJl", "udUJiJwY44O6i2lAos8RmA==");
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testSetVisitorIdManuallyAfterIpAndUserAgent()
    {
        $client = new Client("eHl6Nzg5fDhRZWJ5emlYc2lxVUdlSHpYU0R6YWpQaFVVS2R5czJl", "udUJiJwY44O6i2lAos8RmA==");
        $client->setIpAddress('2.3.4.5');
        $client->setUserAgent('the/crazy/useragent');
        $client->setVisitorId('aabbcc1122334455');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testSetVisitorIdManuallyBeforeIpAndUserAgent()
    {
        $client = new Client("eHl6Nzg5fDhRZWJ5emlYc2lxVUdlSHpYU0R6YWpQaFVVS2R5czJl", "udUJiJwY44O6i2lAos8RmA==");
        $client->setVisitorId('bbccddeeffgghh88776655');
        $client->setIpAddress('4.5.6.7');
        $client->setUserAgent('the/crazy/useragent/2.344');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testTestConnection()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $code = $client->testConnection();
        $this->assertEquals(true, $code);
    }

    public function testTestConnectionErrorIsCaught()
    {
        $client = new Client("cXdlZndlZnx0aGlzSXNOb3RWYWlsZFNlY3JldA==", "4FCAkgNnJ8/N0jkB9r58sQ==");
        $code = $client->testConnection();
        $this->assertEquals(false, $code);
    }
}
