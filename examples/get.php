<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) http client component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

use Amp\Loop;
use Desperado\ServiceBus\HttpClient\Artax\ArtaxHttpClient;
use Desperado\ServiceBus\HttpClient\HttpRequest;

include __DIR__ . '/../vendor/autoload.php';

$client = new ArtaxHttpClient();

Loop::run(
    static function() use ($client)
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = yield $client->execute(HttpRequest::get('https://github.com/mmasiukevich/'));

        echo $response->getStatusCode();
    }
);
