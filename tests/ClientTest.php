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
            "https://abc123.s3y.io/count?ip=1.2.3.4&url=https%3A%2F%2Fwww.schwuppi.de%2Fpapapa%3Fbar%3Dbaz&ref=https%3A%2F%2Fwww.lalala.com%2Fkokoko%3Ffoo%3Dbar&ua=Mozilla%2FSafari%2FChrome";

        $this->assertEquals($expected, $actual);
    }

    public function testAutomaticCollection()
    {
        $Client = new Client("xyz789");
        $actual = $Client->getUrl();

        $expected =
            "https://xyz789.s3y.io/count?ip=2.3.4.5&url=https%3A%2F%2Fwww.this-host-was-called.com%2Fand-this-was-the-path%3Fwith%3Dquery&ref=https%3A%2F%2Fwww.referrer.de%2Fi-refer%3Fbar%3Dfoo&ua=Mozilla%2FSafari%2FChrome%2F1.2";

        $this->assertEquals($expected, $actual);
    }
}
