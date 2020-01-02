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

use Amp\Http\Client\Connection\ConnectionPool;
use Amp\Http\Client\Connection\DefaultConnectionFactory;
use Amp\Http\Client\Connection\UnlimitedConnectionPool;
use Amp\Socket\ConnectContext;

/**
 *
 */
final class ConnectionPollFactory
{
    public static function build(int $connectionTimeout = 60000): ConnectionPool
    {
        $context = new ConnectContext();
        $context = $context->withConnectTimeout($connectionTimeout);

        $connectionFactory = new DefaultConnectionFactory(null, $context);

        return new UnlimitedConnectionPool($connectionFactory);
    }

    private function __construct()
    {
    }
}
