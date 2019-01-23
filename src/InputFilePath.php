<?php

/**
 * PHP Service Bus Http client component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient;

/**
 * Input file path
 */
final class InputFilePath
{
    /**
     * Absolute file path
     *
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->path;
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function fileName(): string
    {
        return \pathinfo($this->path, \PATHINFO_BASENAME);
    }
}
