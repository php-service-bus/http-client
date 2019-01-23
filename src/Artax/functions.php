<?php

/**
 * PHP Service Bus Http client component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Artax;

use Amp\Artax;
use Amp\Promise;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use ServiceBus\HttpClient\Exception as HttpClientExceptions;

/**
 * Download file
 *
 * @api
 *
 * @psalm-suppress MixedTypeCoercion
 *
 * @param string $url
 * @param string $toDirectory
 * @param string $withName
 *
 * @return Promise<string>
 *
 * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
 * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
 * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters
 * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
 */
function downloadFile(string $url, string $toDirectory, string $withName): Promise
{
    return (new ArtaxHttpClient())->download($url, $toDirectory, $withName);
}

/**
 * @internal
 *
 * @param LoggerInterface $logger
 * @param Artax\Request   $request
 * @param string          $requestId
 *
 * @return void
 */
function logArtaxRequest(LoggerInterface $logger, Artax\Request $request, string $requestId): void
{
    $logger->debug(
        'Request: [{requestMethod}] {requestUri} {requestHeaders}', [
            'requestMethod'  => $request->getMethod(),
            'requestUri'     => $request->getUri(),
            'requestHeaders' => $request->getHeaders(),
            'requestId'      => $requestId
        ]
    );
}

/**
 * @internal
 *
 * @param LoggerInterface $logger
 * @param Response        $response
 * @param string          $requestId
 *
 * @return void
 */
function logArtaxResponse(LoggerInterface $logger, Response $response, string $requestId): void
{
    $logger->debug(
        'Response: {responseHttpCode} {responseContent} {responseHeaders}', [
            'responseHttpCode' => $response->getStatusCode(),
            'responseContent'  => (string) $response->getBody(),
            'responseHeaders'  => $response->getHeaders(),
            'requestId'        => $requestId
        ]
    );
}

/**
 * @internal
 *
 * @param LoggerInterface $logger
 * @param \Throwable      $throwable
 * @param string          $requestId
 *
 * @return void
 */
function logArtaxThrowable(LoggerInterface $logger, \Throwable $throwable, string $requestId): void
{
    $logger->error(
        'During the execution of the request with identifier "{requestId}" an exception was caught: "{throwableMessage}"',
        [
            'requestId'        => $requestId,
            'throwableMessage' => $throwable->getMessage(),
            'throwablePoint'   => \sprintf('%s:%d', $throwable->getFile(), $throwable->getLine())
        ]
    );
}

/**
 * @internal
 *
 * @param \Throwable $throwable
 *
 * @return \Throwable
 */
function adaptArtaxThrowable(\Throwable $throwable): \Throwable
{
    /** @var array<class-string<\Amp\Artax\HttpException>, class-string<\Exception>> $mapping */
    $mapping = [
        Artax\DnsException::class     => HttpClientExceptions\DnsResolveFailed::class,
        Artax\SocketException::class  => HttpClientExceptions\ConnectionFailed::class,
        Artax\ParseException::class   => HttpClientExceptions\IncorrectParameters::class,
        Artax\TimeoutException::class => HttpClientExceptions\RequestTimeoutReached::class
    ];

    /** @var class-string<\Amp\Artax\HttpException> $exceptionClass */
    $exceptionClass = \get_class($throwable);

    if(true === isset($mapping[$exceptionClass]))
    {
        /** @var class-string<\Exception> $exceptionClass */
        $exceptionClass = $mapping[$exceptionClass];

        return new $exceptionClass($throwable->getMessage(), (int) $throwable->getCode(), $throwable);
    }

    return new HttpClientExceptions\HttpClientException(
        $throwable->getMessage(),
        (int) $throwable->getCode(),
        $throwable
    );
}
