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
use function Amp\ByteStream\pipe;
use function Amp\call;
use function Amp\File\open;
use function Amp\File\rename;
use Amp\File\StatCache;
use Amp\Promise;
use ServiceBus\HttpClient\HttpClient;
use ServiceBus\HttpClient\HttpRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use GuzzleHttp\Psr7\Response as Psr7Response;

/**
 * Artax (amphp-based) http client
 */
final class ArtaxHttpClient implements HttpClient
{
    /**
     * Artax http client
     *
     * @var Artax\Client
     */
    private $handler;

    /**
     * Logger instance
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param Artax\Client|null    $handler
     * @param int                  $transferTimeout Transfer timeout in milliseconds until an HTTP request is
     *                                              automatically aborted, use 0 to disable
     * @param LoggerInterface|null $logger
     */
    public function __construct(Artax\Client $handler = null, int $transferTimeout = 5000, LoggerInterface $logger = null)
    {
        $this->handler = $handler ?? new Artax\DefaultClient(new Artax\Cookie\ArrayCookieJar());
        $this->logger  = $logger ?? new NullLogger();

        if($transferTimeout > 0 && true === \method_exists($this->handler, 'setOption'))
        {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->handler->setOption(Artax\Client::OP_TRANSFER_TIMEOUT, $transferTimeout);
        }
    }

    /**
     * @inheritdoc
     */
    public function execute(HttpRequest $requestData): Promise
    {
        /** @psalm-suppress InvalidArgument Incorrect psalm unpack parameters (...$args) */
        return call(
            function(HttpRequest $requestData): \Generator
            {
                $generator = 'GET' === $requestData->httpMethod()
                    ? $this->executeGet($requestData)
                    : $this->executePost($requestData);

                return yield from $generator;
            },
            $requestData
        );
    }

    /**
     * @inheritdoc
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName): Promise
    {
        $client = $this->handler;

        /** @psalm-suppress InvalidArgument Incorrect psalm unpack parameters (...$args) */
        return call(
            static function(string $filePath, string $destinationDirectory, string $fileName) use ($client): \Generator
            {
                try
                {
                    /** @var Artax\Response $response */
                    $response         = yield $client->request(new Artax\Request($filePath));
                    $tmpDirectoryPath = \tempnam(\sys_get_temp_dir(), 'artax-streaming-');

                    /** @var \Amp\File\Handle $tmpFile */
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
                catch(\Throwable $throwable)
                {
                    throw adaptArtaxThrowable($throwable);
                }
            },
            $filePath, $destinationDirectory, $fileName
        );
    }

    /**
     * Handle GET query
     *
     * @param HttpRequest $requestData
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     *
     * @throws \Throwable
     */
    private function executeGet(HttpRequest $requestData): \Generator
    {
        $request = (new Artax\Request($requestData->url(), $requestData->httpMethod()))
            ->withHeaders($requestData->headers());

        return self::doRequest(
            $this->handler,
            $request,
            $this->logger
        );
    }

    /**
     * Execute POST request
     *
     * @param HttpRequest $requestData
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     *
     * @throws \Throwable
     */
    private function executePost(HttpRequest $requestData): \Generator
    {
        /** @var ArtaxFormBody|string|null $body */
        $body = $requestData->body();

        $request = (new Artax\Request($requestData->url(), $requestData->httpMethod()))
            ->withBody(
                $body instanceof ArtaxFormBody
                    ? $body->preparedBody()
                    : $body
            )
            ->withHeaders($requestData->headers());

        return self::doRequest($this->handler, $request, $this->logger);
    }

    /**
     * @psalm-suppress InvalidReturnType Incorrect resolving the value of the generator
     *
     * @param Artax\Client    $client
     * @param Artax\Request   $request
     * @param LoggerInterface $logger
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     *
     * @throws \Throwable
     */
    private static function doRequest(Artax\Client $client, Artax\Request $request, LoggerInterface $logger): \Generator
    {
        $requestId = \sha1(random_bytes(32));

        try
        {
            logArtaxRequest($logger, $request, $requestId);

            /** @var Artax\Response $artaxResponse */
            $artaxResponse = yield $client->request($request);

            /** @var Psr7Response $response */
            $response = yield from self::adaptResponse($artaxResponse);

            unset($artaxResponse);

            logArtaxResponse($logger, $response, $requestId);

            return $response;
        }
        catch(\Throwable $throwable)
        {
            logArtaxThrowable($logger, $throwable, $requestId);

            throw adaptArtaxThrowable($throwable);
        }
    }

    /**
     * @noinspection   PhpDocMissingThrowsInspection
     *
     * @psalm-suppress InvalidReturnType Incorrect resolving the value of the generator
     *
     * @param Artax\Response $response
     *
     * @return \Generator<\GuzzleHttp\Psr7\Response>
     */
    private static function adaptResponse(Artax\Response $response): \Generator
    {
        /** @psalm-suppress InvalidCast Invalid read stream handle */
        $responseBody = (string) yield $response->getBody();

        /** @noinspection PhpUnhandledExceptionInspection */
        return new Psr7Response(
            $response->getStatus(),
            $response->getHeaders(),
            $responseBody,
            $response->getProtocolVersion(),
            $response->getReason()
        );
    }
}
