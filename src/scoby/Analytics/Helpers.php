<?php namespace Scoby\Analytics;

class Helpers
{
    public static function getIpAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    public static function getUserAgent() {
        return $_SERVER["HTTP_USER_AGENT"];
    }

    public static function getRequestedUrl() {
        return (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off"
                ? "https"
                : "Http") .
            "://" .
            $_SERVER["HTTP_HOST"] .
            $_SERVER["REQUEST_URI"];
    }

    public static function getReferringUrl() {
        return (!empty($_SERVER["HTTP_REFERER"])
            ? $_SERVER["HTTP_REFERER"]
            : null);
    }
}