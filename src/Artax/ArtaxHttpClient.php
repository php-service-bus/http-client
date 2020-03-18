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

use Amp\Http\Client\Connection\ConnectionPool;
use Amp\Http\Client\Connection\UnlimitedConnectionPool;
use Amp\Http\Client\DelegateHttpClient;
use ServiceBus\HttpClient\RequestContext;
use function Amp\ByteStream\pipe;
use function Amp\call;
use function Amp\File\open;
use function Amp\File\rename;
use Amp\File\StatCache;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Promise;
use Amp\TimeoutCancellationToken;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use ServiceBus\HttpClient\HttpClient;
use ServiceBus\HttpClient\HttpRequest;

/**
 * Artax (amphp-based) http client.
 */
final class ArtaxHttpClient implements HttpClient
{
    /** @var DelegateHttpClient */
    private $handler;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(?ConnectionPool $connectionPool = null, LoggerInterface $logger = null)
    {
        $connectionPool = $connectionPool ?? new UnlimitedConnectionPool();
        $this->logger   = $logger ?? new NullLogger();

        $this->handler = (new HttpClientBuilder())->usingPool($connectionPool)->build();
    }

    /**
     * @psalm-suppress MixedTypeCoercion
     *
     * {@inheritdoc}
     */
    public function execute(HttpRequest $requestData, ?RequestContext $context = null): Promise
    {
        $context = $context ?? new RequestContext();

        /** @psalm-suppress InvalidArgument */
        return call(
            function () use ($requestData, $context): \Generator
            {
                $request = self::buildRequest($requestData, $context);

                /** @var \GuzzleHttp\Psr7\Response $response */
                $response = yield from $this->doRequest($this->handler, $request, $context);

                return $response;
            }
        );
    }

    /**
     * @psalm-suppress MixedTypeCoercion
     *
     * {@inheritdoc}
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName, ?RequestContext $context = null): Promise
    {
        $context = $context ?? new RequestContext();

        /** @psalm-suppress InvalidArgument */
        return call(
            function () use ($filePath, $destinationDirectory, $fileName, $context): \Generator
            {
                try
                {
                    $request = new Request($filePath);
                    $request->setTransferTimeout($context->transferTimeout);
                    $request->setTcpConnectTimeout($context->tcpConnectTimeout);
                    $request->setTlsHandshakeTimeout($context->tlsHandshakeTimeout);

                    if ($context->protocolVersion !== null)
                    {
                        $request->setProtocolVersions([$context->protocolVersion]);
                    }

                    /**
                     * @psalm-suppress TooManyTemplateParams
                     *
                     * @var Response $response
                     */
                    $response = yield $this->handler->request(
                        $request,
                        new TimeoutCancellationToken($context->transferTimeout)
                    );

                    /** @var string $tmpDirectoryPath */
                    $tmpDirectoryPath = \tempnam(\sys_get_temp_dir(), 'artax-streaming-');

                    /** @var \Amp\File\File $tmpFile */
                    $tmpFile = yield open($tmpDirectoryPath, 'w');

                    yield pipe($response->getBody(), $tmpFile);

                    $destinationFilePath = \sprintf(
                        '%s/%s',
                        \rtrim($destinationDirectory, '/'),
                        \ltrim($fileName, '/')
                    );

                    yield $tmpFile->close();
                    yield rename($tmpDirectoryPath, $destinationFilePath);

                    StatCache::clear($tmpDirectoryPath);

                    return $destinationFilePath;
                }
                catch (\Throwable $throwable)
                {
                    throw adaptArtaxThrowable($throwable);
                }
            },
            $filePath,
            $destinationDirectory,
            $fileName
        );
    }

    /**
     * @psalm-suppress InvalidReturnType Incorrect resolving the value of the generator
     *
     * @throws \Throwable
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     */
    private function doRequest(DelegateHttpClient $client, Request $request, RequestContext $context): \Generator
    {
        $timeStart = \microtime(true);

        try
        {
            if ($context->logRequest === true)
            {
                yield from logArtaxRequest($this->logger, $request, $context->traceId);
            }

            /**
             * @psalm-suppress TooManyTemplateParams
             *
             * @var Response $artaxResponse
             */
            $artaxResponse = yield $client->request(
                $request,
                new TimeoutCancellationToken($context->transferTimeout)
            );

            /** @var Psr7Response $response */
            $response = yield from self::adaptResponse($artaxResponse);

            $executionTime = (string) (\microtime(true) - $timeStart);

            if ($context->logResponse === true)
            {
                logArtaxResponse($this->logger, $response, $context->traceId, $executionTime);
            }

            return $response;
        }
        catch (\Throwable $throwable)
        {
            $executionTime = (string) (\microtime(true) - $timeStart);

            logArtaxThrowable($this->logger, $throwable, $context->traceId, $executionTime);

            throw adaptArtaxThrowable($throwable);
        }
    }

    private static function buildRequest(HttpRequest $requestData, RequestContext $context): Request
    {
        $request = new Request($requestData->url, $requestData->method);

        $request->setTransferTimeout($context->transferTimeout);
        $request->setTcpConnectTimeout($context->tcpConnectTimeout);
        $request->setTlsHandshakeTimeout($context->tlsHandshakeTimeout);

        /**
         * @var string          $headerKey
         * @var string|string[] $value
         */
        foreach ($requestData->headers as $headerKey => $value)
        {
            $request->setHeader($headerKey, $value);
        }

        /** @var ArtaxFormBody|string|null $body */
        $body = $requestData->body;

        $request->setBody(
            $body instanceof ArtaxFormBody
                ? $body->preparedBody()
                : $body
        );

        return $request;
    }

    /**
     * @psalm-suppress InvalidReturnType Incorrect resolving the value of the generator
     *
     * @param Response $response
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     */
    private static function adaptResponse(Response $response): \Generator
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         * @psalm-suppress InvalidCast Invalid read stream handle
         */
        $responseBody = (string) yield $response->getBody()->buffer();

        return new Psr7Response(
            $response->getStatus(),
            $response->getHeaders(),
            $responseBody,
            $response->getProtocolVersion(),
            $response->getReason()
        );
    }
}
