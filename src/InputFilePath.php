<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=0);

namespace ServiceBus\HttpClient;

/**
 * Input file path.
 */
final class InputFilePath
{
    /**
     * Absolute file path.
     *
     * @psalm-readonly
     * @psalm-var non-empty-string
     *
     * @var string
     */
    public $path;

    /**
     * @psalm-param non-empty-string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Get file name.
     *
     * @psalm-return non-empty-string
     */
    public function fileName(): string
    {
        /**
         * @noinspection PhpUnnecessaryLocalVariableInspection
         * @noinspection OneTimeUseVariablesInspection
         *
         * @psalm-var non-empty-string $filePath
         */
        $filePath = \pathinfo($this->path, \PATHINFO_BASENAME);

        return $filePath;
    }
}
