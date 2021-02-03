<?php

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
use ServiceBus\HttpClient\InputFilePath;

/**
 *
 */
final class InputFilePathTest extends TestCase
{
    /**
     * @test
     *
     * @throws \Throwable
     */
    public function fileName(): void
    {
        static::assertSame('InputFilePathTest.php', (new InputFilePath(__FILE__))->fileName());
    }
}
