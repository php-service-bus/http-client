<?php

/**
 * Abstraction over Http client implementations
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Tests\Artax;

use function Amp\Promise\wait;
use ServiceBus\HttpClient\Artax\ArtaxHttpClient;
use function ServiceBus\HttpClient\Artax\downloadFile;
use ServiceBus\HttpClient\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 *
 */
final class ArtaxHttpClientSmokeTest extends TestCase
{
    /**
     * @test
     * @expectedException \ServiceBus\HttpClient\Exception\HttpClientException
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function requestWithEmptyURL(): void
    {
        wait((new ArtaxHttpClient())->execute(HttpRequest::get('')));
    }

    /**
     * @test
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function download(): void
    {
        $tmpFilePath = \sys_get_temp_dir() . '/master.zip';

        if(true === \file_exists($tmpFilePath))
        {
            \unlink($tmpFilePath);
        }

        $filePath = wait(downloadFile(
            'https://github.com/mmasiukevich/http-client/archive/master.zip',
            \sys_get_temp_dir(),
            'master.zip'
        ));

        static::assertFileExists($filePath);
        static::assertFileIsReadable($filePath);
        static::assertSame($tmpFilePath, $filePath);

        \unlink($tmpFilePath);
    }

    /**
     * @test
     *
     * @return void
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
     * @expectedException \ServiceBus\HttpClient\Exception\DnsResolveFailed
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function wrongDomain(): void
    {
        wait((new ArtaxHttpClient())->execute(HttpRequest::get('https://segdsgrxdrgdrg.vfs')));
    }
}
