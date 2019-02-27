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

use Amp\ByteStream\InputStream;
use Amp\Promise;

/**
 * Form data.
 */
interface FormBody
{
    /**
     * Create from form parameters.
     *
     * @psalm-param array<string, string|array<string, string>> $fields
     *
     * @param array $fields
     *
     * @return static
     */
    public static function fromParameters(array $fields);

    /**
     * Add a file field to the form entity body.
     *
     * @param string        $fieldName
     * @param InputFilePath $file
     *
     * @return void
     */
    public function addFile(string $fieldName, InputFilePath $file): void;

    /**
     * Add a data field to the form entity body.
     *
     * @param string               $fieldName
     * @param float|integer|string $value
     *
     * @return void
     */
    public function addField(string $fieldName, $value): void;

    /**
     * Add multiple fields/files.
     *
     * @psalm-param array<string, string|int|float|InputFilePath> $fields
     *
     * @param array $fields
     *
     * @return void
     */
    public function addMultiple(array $fields): void;

    /**
     * Create the HTTP message body to be sent.
     *
     * @return InputStream
     */
    public function createBodyStream(): InputStream;

    /**
     * Retrieve a key-value array of headers to add to the outbound request.
     *
     * @psalm-return array<string, string>
     *
     * @return array
     */
    public function headers(): array;

    /**
     * Retrieve the HTTP message body length. If not available, return -1.
     *
     * @return Promise
     */
    public function getBodyLength(): Promise;
}
