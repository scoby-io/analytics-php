# Scoby Analytics PHP Client

[scoby](https://www.scoby.io) is an ethical analytics tool that helps you protect your visitors' privacy without sacrificing meaningful metrics. The data is sourced directly from your web server, no cookies are used and no GDPR, ePrivacy and Schrems II consent is required.

Start your free trial today on [https://app.scoby.io/trial](https://app.scoby.io/trial)

#### Did you know?
scoby is free for non-profit open-source projects.  
[Claim your free account now](mailto:hello@scoby.io?subject=giving%20back)

## Installation
```
composer require scoby/analytics
```

## Usage
The client supports synchronous and asynchronous logging of page views
```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_JAR_ID_HERE');

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

The IP address of the request is considered personal information in some countries, so by default our client does not send the IP address of the request to our servers.
If you want to enable this feature, for example, to allow analysis of the countries of your visitors, you can enable it manually:
```php
// transmit IP address to scoby servers
$client->collectIpAddress(true);
```

### Visitor ID
To help you count your visitors as accurately as possible, you can provide a custom identifier, such as an account id, etc. This value is hashed before being sent to our servers to ensure that no personally identifiable information reaches our servers. However, to be on the safe side, you should talk to your data protection officer before using this feature.
```php
$client->setVisitorId('some-anonymous-identifier');
```

Complete example: 

```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_JAR_ID_HERE');
$client
    ->collectIpAddress(true)
    ->setIpAddress('1.2.3.4')
    ->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:103.0) Gecko/20100101 Firefox/103.0');
    ->setVisitorId('some-anonymous-identifier')
    ->setRequestedUrl('https://example.com/some/path?and=some&query=parameters');
    ->setReferringUrl('https://eyample.com/the/page/that?was=visited&before=yay')
    ->logPageViewAsync();
```

## Testing
```
./vendor/bin/phpunit tests
```

## Support
Something's hard? We're here to help at [hello@scoby.io](mailto:hello@scoby.io)
