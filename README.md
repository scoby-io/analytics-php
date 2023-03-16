# Scoby Analytics PHP Client

[scoby](https://www.scoby.io) is an ethical analytics tool that helps you protect your visitors' privacy without sacrificing meaningful metrics. The data is sourced directly from your web server, no cookies are used and no GDPR, ePrivacy and Schrems II consent is required.

Start your free trial today on [https://app.scoby.io/](https://app.scoby.io/)

#### Did you know?
scoby is free for non-profit open-source projects.  
[Claim your free account now](mailto:hello@scoby.io?subject=giving%20back)

## Installation
```
composer require scoby/analytics
```

## Prerequisites
You need two values to instantiate your scoby analytics client: your API key and a salt. 
The salt is used to anonymize your traffic before it is sent to our servers. 
You can generate a cryptographically secure using the following command: 

````shell
openssl rand -base64 32
````

Please find your API key in your [workspace's settings](https://app.scoby.io) - don't have a workspace yet? Create one for free [here](https://app.scoby.io)

## Usage
Instantiate your scoby analytics client using your API key and salt. 
```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_API_KEY_HERE', 'INSERT_YOUR_SALT_HERE');
```

After that the client supports synchronous and asynchronous logging of page views
```php
// count page view synchronously ...
$client->logPageView(); 

// ... count page view asynchronously
$client->logPageViewAsync(); 
```

Disclaimer: PHP scripts are always blocking. The `logPageViewAsync` executes after all the main work is right before your script exists. 

The client will automatically scan the super global `$_SERVER` variable for all data it needs to work properly. All values can be overridden with fluent setters:

### IP Address
```php
// set IP address manually
$client->setIpAddress('1.2.3.4');
```

### User-Agent
```php
// set User Agent manually
$client->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:103.0) Gecko/20100101 Firefox/103.0');
```

### Requested URL
```php
// set requested URL manually
$client->setRequestedUrl('https://example.com/some/path?and=some&query=parameters');
```

### Referring URL
```php
// set referrer manually
$client->setReferringUrl('https://eyample.com/the/page/that?was=visited&before=yay');
```

### Visitor ID
To help you count your visitors as accurately as possible, you can provide a custom identifier, such as an account id, etc. This value is hashed before being sent to our servers to ensure that no personally identifiable information reaches our servers. However, to be on the safe side, you should talk to your data protection officer before using this feature.
```php
$client->setVisitorId('some-anonymous-identifier');
```

Complete example: 

```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_API_KEY_HERE', 'INSERT_YOUR_SALT_HERE');
$client
    ->setIpAddress('1.2.3.4')
    ->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:103.0) Gecko/20100101 Firefox/103.0')
    ->setVisitorId('some-anonymous-identifier')
    ->setRequestedUrl('https://example.com/some/path?and=some&query=parameters')
    ->setReferringUrl('https://eyample.com/the/page/that?was=visited&before=yay')
    ->logPageViewAsync();
```

### IP Blacklisting
By default, scoby will not exclude any traffic from your measurements, but we understand that sometimes it is necessary 
to filter out traffic originating from a range of IP addresses. To do this, we added the `blacklistIpRange` method, 
which supports wildcard patterns as well as CIDR subnet notation for your convenience. 
You can add as many IPs, patterns and ranges as you like.
```php
$client
    ->blacklistIpRange('12.34.*.*')
    ->blacklistIpRange('87.65.43.21/16')
    ->blacklistIpRange('1.2.3.4')
    ->blacklistIpRange('::1');
```

Complete example: 

```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_API_KEY_HERE', 'INSERT_YOUR_SALT_HERE');
$client
    ->blacklistIpRange('12.34.*.*')
    ->setIpAddress('12.34.56.78') // pattern '12.34.*.*' includes '12.34.56.78'
    ->logPageView(); // returns: false
```

## Testing
```
./vendor/bin/phpunit tests
```

## Support
Something's hard? We're here to help at [hello@scoby.io](mailto:hello@scoby.io)
