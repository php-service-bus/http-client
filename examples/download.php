<?php

/**
 * PHP Service Bus (publish-subscribe pattern) Http client component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

use Amp\Loop;
use function ServiceBus\HttpClient\Artax\downloadFile;

include __DIR__ . '/../vendor/autoload.php';

Loop::run(
    static function()
    {
        /** @var string $filePath */
        $filePath = yield downloadFile(
            'https://github.com/mmasiukevich/service-bus/archive/master.zip',
            \sys_get_temp_dir(),
            'service_bus.zip'
        );

        echo $filePath;
    }
);
