<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient;

/**
 * Input file path.
 */
final class InputFilePath
{
    /**
     * Absolute file path.
     *
     * @var string
     */
    public $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Get file name.
     */
    public function fileName(): string
    {
        return \pathinfo($this->path, \PATHINFO_BASENAME);
    }
}
