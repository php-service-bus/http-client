<?php /** @noinspection PhpUnhandledExceptionInspection */

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
use PHPUnit\Framework\TestCase;
use ServiceBus\HttpClient\Artax\ArtaxFormBody;
use ServiceBus\HttpClient\InputFilePath;

/**
 *
 */
final class ArtaxFormBodyTest extends TestCase
{
    /**
     * @test
     */
    public function addFile(): void
    {
        $formBody = new ArtaxFormBody();
        $formBody->addFile('someField', new InputFilePath(__FILE__));

        $headers = $formBody->headers();

        self::assertArrayHasKey('Content-Type', $headers);
        self::assertSame('multipart/form-data', \explode(';', $headers['Content-Type'])[0]);
    }

    /**
     * @test
     */
    public function fromParametersWithFile(): void
    {
        $formBody = ArtaxFormBody::fromParameters([
            'simpleField' => 'simpleValue',
            'fileField'   => new InputFilePath(__FILE__),
        ]);

        $headers = $formBody->headers();

        self::assertArrayHasKey('Content-Type', $headers);
        self::assertSame('multipart/form-data', \explode(';', $headers['Content-Type'])[0]);
    }

    /**
     * @test
     */
    public function fromParameters(): void
    {
        $formBody = ArtaxFormBody::fromParameters(['simpleField' => 'simpleValue']);
        $headers  = $formBody->headers();

        self::assertArrayHasKey('Content-Type', $headers);
        self::assertSame('application/x-www-form-urlencoded', $headers['Content-Type']);

        self::assertSame(23, wait($formBody->getBodyLength()));
    }
}
