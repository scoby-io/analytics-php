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
        $Client = new Client("abc123");
        $url = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->getUrl();

        $this->assertMatchesSnapshot($url);
    }

    public function testManualSettingOfParamsIncludingIpAddress()
    {
        $Client = new Client("abc123");
        $url = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->collectIpAddress(true)
            ->getUrl();

        $this->assertMatchesSnapshot($url);
    }

    public function testManualSettingOfParamsExcludingIpAddress()
    {
        $Client = new Client("abc123");
        $url = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->collectIpAddress(false)
            ->getUrl();

        $this->assertMatchesSnapshot($url);
    }

    public function testAutomaticCollection()
    {
        $client = new Client("xyz789");
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testAutomaticCollectionIncludingIpAddress()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(true);
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testAutomaticCollectionExcludingIpAddress()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(false);
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testSetVisitorIdManuallyAfterIpAndUserAgent()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(true);
        $client->setIpAddress('2.3.4.5');
        $client->setUserAgent('the/crazy/useragent');
        $client->setVisitorId('aabbcc1122334455');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function testSetVisitorIdManuallyBeforeIpAndUserAgent()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(true);
        $client->setVisitorId('bbccddeeffgghh88776655');
        $client->setIpAddress('4.5.6.7');
        $client->setUserAgent('the/crazy/useragent/2.344');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }
}
