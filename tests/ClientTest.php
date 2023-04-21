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
        $_SERVER["REQUEST_URI"] = "/and-this-was-the-path?with=query&utm_source=adnetwork";
    }

    public function test_manual_setting_of_params()
    {
        $Client = new Client("YWJjMTIzfFE0YWt5MEhXb0t4N28xdXRCRnlRY2x5WnBRdTJZR0J1", "m7cQPvnI8CqwrSDfA1Ynrg==");
        $url = $Client
            ->setIpAddress("1.2.3.4")
            ->setUserAgent("Mozilla/Safari/Chrome")
            ->setReferringUrl("https://www.lalala.com/kokoko?foo=bar")
            ->setRequestedUrl("https://www.schwuppi.de/papapa?bar=baz&gclid=1122334455")
            ->getUrl();

        $this->assertMatchesSnapshot($url);
    }

    public function test_automatic_collection()
    {
        $client = new Client("eHl6Nzg5fDhRZWJ5emlYc2lxVUdlSHpYU0R6YWpQaFVVS2R5czJl", "udUJiJwY44O6i2lAos8RmA==");
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function test_set_visitor_id_manually_after_ip_and_useragent()
    {
        $client = new Client("eHl6Nzg5fDhRZWJ5emlYc2lxVUdlSHpYU0R6YWpQaFVVS2R5czJl", "udUJiJwY44O6i2lAos8RmA==");
        $client->setIpAddress('2.3.4.5');
        $client->setUserAgent('the/crazy/useragent');
        $client->setVisitorId('aabbcc1122334455');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function test_set_visitor_id_manually_before_ip_and_useragent()
    {
        $client = new Client("eHl6Nzg5fDhRZWJ5emlYc2lxVUdlSHpYU0R6YWpQaFVVS2R5czJl", "udUJiJwY44O6i2lAos8RmA==");
        $client->setVisitorId('bbccddeeffgghh88776655');
        $client->setIpAddress('4.5.6.7');
        $client->setUserAgent('the/crazy/useragent/2.344');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function test_get_api_status()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $res = $client->getApiStatus();
        $body = json_decode($res->getBody());
        $this->assertIsObject($body);
    }

    public function test_get_api_status_error_is_caught()
    {
        $this->expectException(\GuzzleHttp\Exception\RequestException::class);
        $client = new Client("cXdlZndlZnx0aGlzSXNOb3RWYWlsZFNlY3JldA==", "4FCAkgNnJ8/N0jkB9r58sQ==");
        $client->getApiStatus();
    }

    public function test_test_connection()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $code = $client->testConnection();
        $this->assertEquals(true, $code);
    }

    public function test_test_connectio_error_is_caught()
    {
        $client = new Client("cXdlZndlZnx0aGlzSXNOb3RWYWlsZFNlY3JldA==", "4FCAkgNnJ8/N0jkB9r58sQ==");
        $code = $client->testConnection();
        $this->assertEquals(false, $code);
    }

    public function test_blacklist_ip_range_excludes_ip()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $client->blacklistIpRange('127.0.0.*');
        $client->setIpAddress('127.0.0.3');
        $this->assertEquals($client->logPageView(), false);
    }

    public function test_blacklist_ip_range_does_not_exclude_valid_ip()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $client->blacklistIpRange('127.0.0.*');
        $client->setIpAddress('1.1.1.1');
        $this->assertEquals($client->logPageView(), true);
    }

    public function test_whitelisted_query_parameter_is_whitlisted()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $client->whitelistQueryParameter('with');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function test_add_visitor_trait()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $client->addVisitorToSegment('Subscribers');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }

    public function test_add_visitor_traits()
    {
        $client = new Client("dzJkeGV8TDlST1hMaXozMVFtd2o4U3hmQVIzQWxNOFh1dWZZTno=", "4GKyOvsn5GVUG+REbzspEA==");
        $client->addVisitorToSegment('Women');
        $client->addVisitorToSegment('Young Adults');
        $url = $client->getUrl();
        $this->assertMatchesSnapshot($url);
    }
}
