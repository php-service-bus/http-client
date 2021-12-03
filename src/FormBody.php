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
     * @psalm-param array<non-empty-string, float|int|string|InputFilePath> $fields
     */
    public static function fromParameters(array $fields): self;

    /**
     * Add a file field to the form entity body.
     *
     * @psalm-param non-empty-string $contentType
     */
    public function addFile(string $fieldName, InputFilePath $file, string $contentType = 'application/octet-stream'): void;

    /**
     * Add a data field to the form entity body.
     */
    public function addField(string $fieldName, float|int|string $value): void;

    /**
     * Add multiple fields/files.
     *
     * @psalm-param array<string, float|int|string|InputFilePath> $fields
     */
    public function addMultiple(array $fields): void;

    /**
     * Create the HTTP message body to be sent.
     */
    public function createBodyStream(): InputStream;

    /**
     * Retrieve a key-value array of headers to add to the outbound request.
     *
     * @psalm-return array<non-empty-string, array|string>
     */
    public function headers(): array;

    /**
     * Retrieve the HTTP message body length. If not available, return -1.
     *
     * @return Promise<int>
     */
    public function getBodyLength(): Promise;
}
