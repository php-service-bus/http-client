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

    private const DEFAULT_FOLLOW_REDIRECTS = 10;

    private AmphpHttpClient $handler;

    private LoggerInterface $logger;

    public function __construct(?AmphpHttpClient $client = null, LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();

        if (null === $client)
        {
            $client = (new HttpClientBuilder())
                ->followRedirects(self::DEFAULT_FOLLOW_REDIRECTS)
                ->build();
        }

        $this->handler = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(HttpRequest $requestData): Promise
    {
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
     * {@inheritdoc}
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName): Promise
    {
        $client = $this->handler;

        return call(
            static function (string $filePath, string $destinationDirectory, string $fileName) use ($client): \Generator
            {
                try
                {
                    /**
                     * @psalm-suppress TooManyTemplateParams
                     *
                     * @var Response $response
                     */
                    $response = yield $client->request(
                        new Request($filePath),
                        new TimeoutCancellationToken(self::DEFAULT_TRANSFER_TIMEOUT)
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

        return self::doRequest(
            $this->handler,
            $request,
            $this->logger
        );
    }

    /**
     * Execute POST request.
     */
    private function executePost(HttpRequest $requestData): \Generator
    {
        /** @var ArtaxFormBody|string|null $body */
        $body = $requestData->body;

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

        return self::doRequest($this->handler, $request, $this->logger);
    }

    /**
     * @throws \Throwable
     */
    private static function doRequest(AmphpHttpClient $client, Request $request, LoggerInterface $logger): \Generator
    {
        $requestId = \sha1(random_bytes(32));

        try
        {
            yield from logArtaxRequest($logger, $request, $requestId);

            /**
             * @psalm-suppress TooManyTemplateParams
             *
             * @var \Amp\Http\Client\Response $artaxResponse
             */
            $artaxResponse = yield $client->request(
                $request,
                new TimeoutCancellationToken(self::DEFAULT_TRANSFER_TIMEOUT)
            );

            /** @var Psr7Response $response */
            $response = yield from self::adaptResponse($artaxResponse);

            unset($artaxResponse);

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
     * @psalm-suppress InvalidReturnType
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
