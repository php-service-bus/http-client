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

use Amp\Http\Client\Connection\UnlimitedConnectionPool;
use function Amp\ByteStream\pipe;
use function Amp\call;
use function Amp\File\open;
use function Amp\File\rename;
use Amp\File\StatCache;
use Amp\Http\Client\HttpClient as AmphpHttpClient;
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
    private const DEFAULT_TRANSFER_TIMEOUT = 10000;

    /** @var AmphpHttpClient */
    private $handler;

    /** @var LoggerInterface */
    private $logger;

    /** @var int */
    private $transferTimeout;

    /**
     * @param AmphpHttpClient      $httpClient
     * @param int|null             $transferTimeout Transfer timeout in milliseconds until an HTTP request is
     *                                              automatically aborted, use 0 to disable
     * @param LoggerInterface|null $logger
     */
    public function __construct(AmphpHttpClient $httpClient = null, ?int $transferTimeout = null, LoggerInterface $logger = null)
    {
        $this->logger          = $logger ?? new NullLogger();
        $this->transferTimeout = $transferTimeout ?? self::DEFAULT_TRANSFER_TIMEOUT;

        $this->handler = $httpClient ?? (new HttpClientBuilder())
                ->usingPool(new UnlimitedConnectionPool())
                ->build();
    }

    /**
     * @psalm-suppress MixedTypeCoercion
     *
     * {@inheritdoc}
     */
    public function execute(HttpRequest $requestData): Promise
    {
        /** @psalm-suppress InvalidArgument */
        return call(
            function (HttpRequest $requestData): \Generator
            {
                $generator = 'GET' === $requestData->method
                    ? $this->executeGet($requestData)
                    : $this->executePost($requestData);

                return yield from $generator;
            },
            $requestData
        );
    }

    /**
     * @psalm-suppress MixedTypeCoercion
     *
     * {@inheritdoc}
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName): Promise
    {
        /** @psalm-suppress InvalidArgument */
        return call(
            function (string $filePath, string $destinationDirectory, string $fileName): \Generator
            {
                try
                {
                    /**
                     * @psalm-suppress TooManyTemplateParams
                     *
                     * @var Response $response
                     */
                    $response = yield $this->handler->request(
                        new Request($filePath),
                        new TimeoutCancellationToken($this->transferTimeout)
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
     * Handle GET query.
     *
     * @param HttpRequest $requestData
     *
     * @throws \Throwable
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     */
    private function executeGet(HttpRequest $requestData): \Generator
    {
        $request = new Request($requestData->url);

        /**
         * @var string          $headerKey
         * @var string|string[] $value
         */
        foreach ($requestData->headers as $headerKey => $value)
        {
            $request->setHeader($headerKey, $value);
        }

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = yield from $this->doRequest($this->handler, $request, $this->logger);

        return $response;
    }

    /**
     * Execute POST request.
     *
     * @param HttpRequest $requestData
     *
     * @throws \Throwable
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     */
    private function executePost(HttpRequest $requestData): \Generator
    {
        /** @var ArtaxFormBody|string|null $body */
        $body    = $requestData->body;
        $request = new Request($requestData->url, $requestData->method);

        $request->setBody(
            $body instanceof ArtaxFormBody
                ? $body->preparedBody()
                : $body
        );

        /**
         * @var string          $headerKey
         * @var string|string[] $value
         */
        foreach ($requestData->headers as $headerKey => $value)
        {
            $request->setHeader($headerKey, $value);
        }

        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = yield from $this->doRequest($this->handler, $request, $this->logger);

        return $response;
    }

    /**
     * @psalm-suppress InvalidReturnType Incorrect resolving the value of the generator
     *
     * @param AmphpHttpClient $client
     * @param Request         $request
     * @param LoggerInterface $logger
     *
     * @throws \Throwable
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     */
    private function doRequest(AmphpHttpClient $client, Request $request, LoggerInterface $logger): \Generator
    {
        $requestId = \sha1(random_bytes(32));

        try
        {
            logArtaxRequest($logger, $request, $requestId);

            /**
             * @psalm-suppress TooManyTemplateParams
             *
             * @var Response $artaxResponse
             */
            $artaxResponse = yield $client->request(
                $request,
                new TimeoutCancellationToken($this->transferTimeout)
            );

            /** @var Psr7Response $response */
            $response = yield from self::adaptResponse($artaxResponse);

            logArtaxResponse($logger, $response, $requestId);

            return $response;
        }
        catch (\Throwable $throwable)
        {
            logArtaxThrowable($logger, $throwable, $requestId);

            throw adaptArtaxThrowable($throwable);
        }
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
        /** @psalm-suppress InvalidCast Invalid read stream handle */
        $responseBody = (string) yield $response->getBody()->read();

        return new Psr7Response(
            $response->getStatus(),
            $response->getHeaders(),
            $responseBody,
            $response->getProtocolVersion(),
            $response->getReason()
        );
    }
}
