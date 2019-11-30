<?php

/**
 * PHP Service Bus Http client component.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Tests;

use PHPUnit\Framework\TestCase;
use ServiceBus\HttpClient\Artax\ArtaxFormBody;
use ServiceBus\HttpClient\HttpRequest;
use ServiceBus\HttpClient\InputFilePath;

/**
 *
 */
final class HttpRequestTest extends TestCase
{
    /**
     * @test
     *
     * @throws \Throwable
     */
    public function createWithFormBody(): void
    {
        $body = new ArtaxFormBody();

        $body->addFile('someFile', new InputFilePath(__FILE__));
        $body->addField('qwerty', 'root');

        $requestData = HttpRequest::post('https://google.com', $body, ['key' => 'value']);

        static::assertArrayHasKey('Content-Type', $requestData->headers);
        static::assertArrayHasKey('key', $requestData->headers);

        static::assertSame('multipart/form-data', \explode(';', $requestData->headers['Content-Type'])[0]);
        static::assertSame('value', $requestData->headers['key']);

        static::assertTrue($requestData->isPost());
    }
}
