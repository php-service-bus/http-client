<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Tests\Artax;

use function Amp\Promise\wait;
use function ServiceBus\HttpClient\Artax\downloadFile;
use PHPUnit\Framework\TestCase;
use ServiceBus\HttpClient\Artax\ArtaxHttpClient;
use ServiceBus\HttpClient\Exception\DnsResolveFailed;
use ServiceBus\HttpClient\Exception\HttpClientException;
use ServiceBus\HttpClient\HttpRequest;

/**
 *
 */
final class ArtaxHttpClientSmokeTest extends TestCase
{
    /**
     * @test
     *
     * @throws \Throwable
     */
    public function requestWithEmptyURL(): void
    {
        $this->expectException(HttpClientException::class);

        wait((new ArtaxHttpClient())->execute(HttpRequest::get('')));
    }

    /**
     * @test
     *
     * @throws \Throwable
     */
    public function download(): void
    {
        $tmpFilePath = \sys_get_temp_dir() . '/master.zip';

        if (true === \file_exists($tmpFilePath))
        {
            \unlink($tmpFilePath);
        }

        $filePath = wait(downloadFile(
            'https://github.com/php-service-bus/http-client/archive/v3.3.0.zip',
            \sys_get_temp_dir(),
            'master.zip'
        ));

        static::assertFileExists($filePath);
        static::assertFileIsReadable($filePath);
        static::assertSame($tmpFilePath, $filePath);

        //   \unlink($tmpFilePath);
    }

    /**
     * @test
     *
     * @throws \Throwable
     */
    public function postRequest(): void
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = wait((new ArtaxHttpClient())->execute(HttpRequest::post('https://google.com', 'qwerty')));

        static::assertSame(405, $response->getStatusCode());
    }

    /**
     * @test
     *
     * @throws \Throwable
     */
    public function wrongDomain(): void
    {
        $this->expectException(DnsResolveFailed::class);

        wait((new ArtaxHttpClient())->execute(HttpRequest::get('https://segdsgrxdrgdrg.vfs')));
    }
}
