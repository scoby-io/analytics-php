<?php

use PHPUnit\Framework\TestCase;
use Scoby\Analytics\Client;

class ClientTest extends TestCase
{
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
        $actual = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->getUrl();

        $expected =
            "https://abc123.s3y.io/count?vid=43c34e10302f7971108510a11b5b65c8bb1b8881d24d074fa1fef1845c7e71be&url=https%3A%2F%2Fwww.schwuppi.de%2Fpapapa%3Fbar%3Dbaz&ref=https%3A%2F%2Fwww.lalala.com%2Fkokoko%3Ffoo%3Dbar&ua=Mozilla%2FSafari%2FChrome";

        $this->assertEquals($expected, $actual);
    }

    public function testManualSettingOfParamsIncludingIpAddress()
    {
        $Client = new Client("abc123");
        $actual = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->collectIpAddress(true)
            ->getUrl();

        $expected =
            "https://abc123.s3y.io/count?vid=43c34e10302f7971108510a11b5b65c8bb1b8881d24d074fa1fef1845c7e71be&url=https%3A%2F%2Fwww.schwuppi.de%2Fpapapa%3Fbar%3Dbaz&ref=https%3A%2F%2Fwww.lalala.com%2Fkokoko%3Ffoo%3Dbar&ua=Mozilla%2FSafari%2FChrome&ip=1.2.3.4";

        $this->assertEquals($expected, $actual);
    }

    public function testManualSettingOfParamsExcludingIpAddress()
    {
        $Client = new Client("abc123");
        $actual = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz")
            ->collectIpAddress(false)
            ->getUrl();

        $expected =
            "https://abc123.s3y.io/count?vid=43c34e10302f7971108510a11b5b65c8bb1b8881d24d074fa1fef1845c7e71be&url=https%3A%2F%2Fwww.schwuppi.de%2Fpapapa%3Fbar%3Dbaz&ref=https%3A%2F%2Fwww.lalala.com%2Fkokoko%3Ffoo%3Dbar&ua=Mozilla%2FSafari%2FChrome";

        $this->assertEquals($expected, $actual);
    }

    public function testAutomaticCollection()
    {
        $Client = new Client("xyz789");
        $actual = $Client->getUrl();

        $expected =
            "https://xyz789.s3y.io/count?vid=2dc4e4a6b96b8349edd6bad627dccee301389f0d92d528ce625f38a5819d7aca&url=https%3A%2F%2Fwww.this-host-was-called.com%2Fand-this-was-the-path%3Fwith%3Dquery&ref=https%3A%2F%2Fwww.referrer.de%2Fi-refer%3Fbar%3Dfoo&ua=Mozilla%2FSafari%2FChrome%2F1.2";

        $this->assertEquals($expected, $actual);
    }

    public function testAutomaticCollectionIncludingIpAddress()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(true);
        $actual = $client->getUrl();

        $expected =
            "https://xyz789.s3y.io/count?vid=2dc4e4a6b96b8349edd6bad627dccee301389f0d92d528ce625f38a5819d7aca&url=https%3A%2F%2Fwww.this-host-was-called.com%2Fand-this-was-the-path%3Fwith%3Dquery&ref=https%3A%2F%2Fwww.referrer.de%2Fi-refer%3Fbar%3Dfoo&ua=Mozilla%2FSafari%2FChrome%2F1.2&ip=2.3.4.5";

        $this->assertEquals($expected, $actual);
    }

    public function testAutomaticCollectionExcludingIpAddress()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(false);
        $actual = $client->getUrl();

        $expected =
            "https://xyz789.s3y.io/count?vid=2dc4e4a6b96b8349edd6bad627dccee301389f0d92d528ce625f38a5819d7aca&url=https%3A%2F%2Fwww.this-host-was-called.com%2Fand-this-was-the-path%3Fwith%3Dquery&ref=https%3A%2F%2Fwww.referrer.de%2Fi-refer%3Fbar%3Dfoo&ua=Mozilla%2FSafari%2FChrome%2F1.2";

        $this->assertEquals($expected, $actual);
    }

    public function testSetVisitorIdManuallyAfterIpAndUserAgent()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(true);
        $client->setIpAddress('2.3.4.5');
        $client->setUserAgent('the/crazy/useragent');
        $client->setVisitorId('aabbcc1122334455');
        $actual = $client->getUrl();

        $expected =
            "https://xyz789.s3y.io/count?vid=125ad3ef26fc017a8bd4cfc36cec02d1dfb6d0193dbd14ae78b0cdaa0500a051&url=https%3A%2F%2Fwww.this-host-was-called.com%2Fand-this-was-the-path%3Fwith%3Dquery&ref=https%3A%2F%2Fwww.referrer.de%2Fi-refer%3Fbar%3Dfoo&ua=the%2Fcrazy%2Fuseragent&ip=2.3.4.5";

        $this->assertEquals($expected, $actual);
    }

    public function testSetVisitorIdManuallyBeforeIpAndUserAgent()
    {
        $client = new Client("xyz789");
        $client->collectIpAddress(true);
        $client->setVisitorId('bbccddeeffgghh88776655');
        $client->setIpAddress('4.5.6.7');
        $client->setUserAgent('the/crazy/useragent/2.344');
        $actual = $client->getUrl();

        $expected =
            "https://xyz789.s3y.io/count?vid=b0bd6163311cf6ce97a3d25abaf12ce4694782e705dcf522a980d937e94ea319&url=https%3A%2F%2Fwww.this-host-was-called.com%2Fand-this-was-the-path%3Fwith%3Dquery&ref=https%3A%2F%2Fwww.referrer.de%2Fi-refer%3Fbar%3Dfoo&ua=the%2Fcrazy%2Fuseragent%2F2.344&ip=4.5.6.7";

        $this->assertEquals($expected, $actual);
    }
}
