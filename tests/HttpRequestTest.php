<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * PHP Service Bus Http client component.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
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
     */
    public function createWithFormBody(): void
    {
        $body = new ArtaxFormBody();

        $body->addFile('someFile', new InputFilePath(__FILE__));
        $body->addField('qwerty', 'root');

        $requestData = HttpRequest::post('https://google.com', $body, ['key' => 'value']);

        self::assertArrayHasKey('Content-Type', $requestData->headers);
        self::assertArrayHasKey('key', $requestData->headers);

        self::assertSame('multipart/form-data', \explode(';', $requestData->headers['Content-Type'])[0]);
        self::assertSame('value', $requestData->headers['key']);

        self::assertTrue($requestData->isPost());
    }
}
