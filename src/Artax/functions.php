<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Artax;

use Amp\CancelledException;
use Amp\Dns\DnsException;
use Amp\Dns\NoRecordException;
use Amp\Http\Client\ParseException;
use Amp\Http\Client\Request;
use Amp\Http\Client\SocketException;
use Amp\Http\Client\TimeoutException;
use Amp\Promise;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use ServiceBus\HttpClient\Exception as HttpClientExceptions;

/**
 * Download file.
 *
 * @api
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
 * @psalm-suppress InvalidReturnType
 *
 * @internal
 */
function logArtaxRequest(LoggerInterface $logger, Request $request, string $requestId): \Generator
{
    $logger->debug(
        'Request: [{requestMethod}] {requestUri} {requestHeaders}',
        [
            'requestMethod'  => $request->getMethod(),
            'requestUri'     => $request->getUri(),
            'requestContent' => yield $request->getBody()->createBodyStream()->read(),
            'requestHeaders' => $request->getHeaders(),
            'requestId'      => $requestId,
        ]
    );
}

/**
 * @internal
 *
 * @return void
 */
function logArtaxResponse(LoggerInterface $logger, Response $response, string $requestId): void
{
    $logger->debug(
        'Response: {responseHttpCode} {responseContent} {responseHeaders}',
        [
            'responseHttpCode' => $response->getStatusCode(),
            'responseContent'  => (string) $response->getBody(),
            'responseHeaders'  => $response->getHeaders(),
            'requestId'        => $requestId,
        ]
    );
}

/**
 * @internal
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
            'throwablePoint'   => \sprintf('%s:%d', $throwable->getFile(), $throwable->getLine()),
        ]
    );
}

/**
 * @internal
 *
 * @return \Throwable
 */
function adaptArtaxThrowable(\Throwable $throwable): \Throwable
{
    /** @psalm-var array<class-string<\Amp\Http\Client\HttpException>, class-string<\Exception>> $mapping */
    $mapping = [
        NoRecordException::class  => HttpClientExceptions\DnsResolveFailed::class,
        DnsException::class       => HttpClientExceptions\DnsResolveFailed::class,
        SocketException::class    => HttpClientExceptions\ConnectionFailed::class,
        ParseException::class     => HttpClientExceptions\IncorrectParameters::class,
        TimeoutException::class   => HttpClientExceptions\RequestTimeoutReached::class,
        CancelledException::class => HttpClientExceptions\RequestTimeoutReached::class,
    ];

    /** @psalm-var class-string<\Amp\Http\Client\HttpException> $exceptionClass */
    $exceptionClass = \get_class($throwable);

    if (true === isset($mapping[$exceptionClass]))
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
