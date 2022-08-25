# Scoby Analytics PHP Client

[scoby](https://www.scoby.io) is an ethical analytics tool that helps you protecting your visitors' privacy without compromising on meaningful metrics. Data is sourced from your webserver, uses no cookies and requires no consent in regards to GDPR, ePrivacy and Schrems II.

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
// set IP address manually
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

Complete example: 

```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_JAR_ID_HERE');
$client
    ->setIpAddress('1.2.3.4')
    ->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:103.0) Gecko/20100101 Firefox/103.0');
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
