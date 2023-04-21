# Scoby Analytics: PHP Client

[scoby](https://www.scoby.io) is an ethical analytics tool that helps you protect your visitors' privacy without sacrificing meaningful metrics. The data is sourced directly from your web server, no cookies are used and no GDPR, ePrivacy and Schrems II consent is required.

Start your free trial today on [https://app.scoby.io/](https://app.scoby.io/)

#### Did you know?
Scoby is available for free for non-profit open-source projects.  
[Request your free account now](mailto:hello@scoby.io?subject=giving%20back)


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

Disclaimer: PHP scripts are always blocking. The `logPageViewAsync` executes after all the main work is done right before your script exits. 

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


### Visitor Segments
Visitor segmentation is a powerful tool for improving your website's performance and user engagement. By dividing your audience into different segments based on their common characteristics or behaviors, you can better understand their needs and tailor your website content, marketing messages, and user experience to meet their specific preferences.

To use visitor segments, you need to define the characteristics or behaviors that you want to segment your audience by, such as age, gender, location, interests, or purchase history. Once you have defined your segments, you can assign them to your website visitors based on the data you collect from their interactions with your website. It's important to note that segments should always be used in plural, as this makes the most sense in the Scoby Analytics dashboard, where you can view and analyze your visitor segments data. Additionally, all future interactions of a visitor are associated with the segment they belong to at the time of the interaction.

To log visitor segments using the Scoby Analytics library in your PHP code, you can use the addVisitorToSegment method to assign a visitor to a specific segment, and the logPageView method to log a page view event with the assigned segments. Here's an example:

```php
use Scoby\Analytics\Client;
$client = new Client('INSERT_YOUR_API_KEY_HERE', 'INSERT_YOUR_SALT_HERE');
$client
    ->addVisitorToSegment('Subscribers')
    ->addVisitorToSegment('Women')
    ->addVisitorToSegment('Young Adults')
    ->logPageView();
```

In this example, we are logging a page view event and assigning the visitor segments Subscribers, Women, and Young Adults to the visitor who triggered the event. You can replace these segments with your own segment names based on your segmentation strategy.

Scoby Analytics is designed to be privacy-preserving, and applies a k-Anonymity of 25 to visitor segments. This means that each segment must have at least 25 unique visitors before it is included in the analytics report, in order to protect the anonymity of individual visitors.

### Visitor ID
To enhance the accuracy of visitor counting, you have the option to supply a custom identifier, such as an account ID or similar value. Before being transmitted to our servers, this identifier is hashed to prevent the sharing of personally identifiable information. Nonetheless, it is crucial to consult your data protection officer to ensure that using this feature aligns with your organization's legal and privacy requirements.
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
By default, Scoby does not exclude any traffic from your measurements. However, we understand that you may need to filter out traffic from specific IP addresses or ranges. The `blacklistIpRange` method allows you to do this, supporting both wildcard patterns and CIDR subnet notation for your convenience. You can add multiple IPs, patterns, and ranges as needed.
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
