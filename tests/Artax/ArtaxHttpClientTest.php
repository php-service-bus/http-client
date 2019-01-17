<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) http client component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\HttpClient\Tests\Artax;

use function Amp\Promise\wait;
use Desperado\ServiceBus\HttpClient\Artax\ArtaxHttpClient;
use Desperado\ServiceBus\HttpClient\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 *
 */
final class ArtaxHttpClientTest extends TestCase
{
    /**
     * @test
     * @expectedException \Desperado\ServiceBus\HttpClient\Exception\HttpClientException
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function requestWithEmptyURL(): void
    {
        wait((new ArtaxHttpClient())->execute(HttpRequest::get('')));
    }
}
