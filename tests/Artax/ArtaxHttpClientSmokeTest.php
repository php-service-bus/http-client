<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace ServiceBus\HttpClient\Tests\Artax;

use PHPUnit\Framework\TestCase;
use ServiceBus\HttpClient\Artax\ArtaxHttpClient;
use ServiceBus\HttpClient\Exception\DnsResolveFailed;
use ServiceBus\HttpClient\Exception\HttpClientException;
use ServiceBus\HttpClient\HttpRequest;
use function Amp\Promise\wait;
use function ServiceBus\HttpClient\Artax\downloadFile;

/**
 *
 */
final class ArtaxHttpClientSmokeTest extends TestCase
{
    /**
     * @test
     */
    public function requestWithEmptyURL(): void
    {
        $this->expectException(HttpClientException::class);

        wait(ArtaxHttpClient::build()->execute(HttpRequest::get('')));
    }

    /**
     * @test
     */
    public function download(): void
    {
        $tmpFilePath = \sys_get_temp_dir() . '/master.zip';

        if (\file_exists($tmpFilePath))
        {
            \unlink($tmpFilePath);
        }

        $filePath = wait(downloadFile(
            'https://github.com/php-service-bus/http-client/archive/v3.3.0.zip',
            \sys_get_temp_dir(),
            'master.zip'
        ));

        self::assertFileExists($filePath);
        self::assertFileIsReadable($filePath);
        self::assertSame($tmpFilePath, $filePath);

        unlink($tmpFilePath);
    }

    /**
     * @test
     */
    public function postRequest(): void
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = wait(ArtaxHttpClient::build()->execute(HttpRequest::post('https://google.com', 'qwerty')));

        self::assertSame(405, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function wrongDomain(): void
    {
        $this->expectException(DnsResolveFailed::class);

        wait(ArtaxHttpClient::build()->execute(HttpRequest::get('https://segdsgrxdrgdrg.vfs')));
    }
}
