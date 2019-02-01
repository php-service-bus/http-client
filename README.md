[![Build Status](https://travis-ci.org/php-service-bus/http-client.svg?branch=master)](https://travis-ci.org/php-service-bus/http-client)
[![Code Coverage](https://scrutinizer-ci.com/g/php-service-bus/http-client/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/php-service-bus/http-client/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/php-service-bus/http-client/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/php-service-bus/http-client/?branch=master)

## What is it?

Abstraction over Http client implementations.

## Usage

Request parameters are formed using the [HttpRequest](https://github.com/php-service-bus/http-client/blob/master/src/HttpRequest.php) structure. The http request execution adapter must implement the [HttpClient](https://github.com/php-service-bus/http-client/blob/master/src/HttpClient.php) interface

## Examples:

#### Simple GET request (using [Artax](https://github.com/amphp/artax)):
```php
$client = new ArtaxHttpClient();

Loop::run(
    static function() use ($client)
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = yield $client->execute(HttpRequest::get('https://github.com/php-service-bus/'));

        echo $response->getStatusCode();
    }
);
```

#### File download (using [Artax](https://github.com/amphp/artax)):

```php
$client = new ArtaxHttpClient();

Loop::run(
    static function() use ($client)
    {
        /** @var string $filePath */
        $filePath = yield $client->download(
            'https://github.com/mmasiukevich/service-bus/archive/master.zip',
            \sys_get_temp_dir(),
            'service_bus.zip'
        );

        echo $filePath;
    }
);
```
Or
```php
Loop::run(
    static function()
    {
        /** @var string $filePath */
        $filePath = yield downloadFile(
            'https://github.com/mmasiukevich/service-bus/archive/master.zip',
            \sys_get_temp_dir(),
            'service_bus.zip'
        );

        echo $filePath;
    }
);
```

