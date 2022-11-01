<?php namespace Scoby\Analytics;

use Exception;

class Helpers
{
    /**
     * @throws Exception
     */
    public static function getIpAddress(): string
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
        throw new Exception('Failed to determine IP address automatically. Please supply a valid IP address using the `setIpAddress` method.');
    }

    /**
     * @throws Exception
     */
    public static function getUserAgent(): string
    {
        if($_SERVER["HTTP_USER_AGENT"]) {
            return $_SERVER["HTTP_USER_AGENT"];
        }
        throw new Exception('Failed to determine User Agent automatically. Please supply a valid User Agent using the `setUserAgent` method.');
    }

    /**
     * @throws Exception
     */
    public static function getRequestedUrl(): string
    {
        $url = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
                ? "https"
                : "http") .
            "://" .
            $_SERVER["HTTP_HOST"] .
            $_SERVER["REQUEST_URI"];
        if(filter_var($url, FILTER_VALIDATE_URL) !== false) {
            return $url;
        }
        throw new Exception('Failed to determine requested URL automatically. Please supply a valid URL using the `setRequestedUrl` method.');
    }

    /**
     * @throws Exception
     */
    public static function getReferringUrl(): ?string
    {
        $url = (!empty($_SERVER["HTTP_REFERER"])
            ? $_SERVER["HTTP_REFERER"]
            : null);

        if($url) {
            if(filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return $url;
            }
            throw new Exception('Failed to determine referring URL automatically. Please supply a valid URL using the `setReferringUrl` method.');
        }

        return null;
    }
}