<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 0);

namespace ServiceBus\HttpClient\Artax;

use Amp\ByteStream\InputStream;
use Amp\Http\Client\Body\FormBody as AmpFormBody;
use Amp\Promise;
use ServiceBus\HttpClient\FormBody;
use ServiceBus\HttpClient\InputFilePath;

/**
 * Artax form body implementation.
 */
final class ArtaxFormBody implements FormBody
{
    /**
     * Original body object.
     *
     * @var AmpFormBody
     */
    private $original;

    /**
     * Boundary.
     *
     * @var string
     */
    private $boundary;

    /**
     * Is multipart request.
     *
     * @var bool
     */
    private $isMultipart;

    public function __construct()
    {
        $this->isMultipart = false;

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->boundary = \bin2hex(\random_bytes(16));
        $this->original = new AmpFormBody($this->boundary);
    }

    public static function fromParameters(array $fields): ArtaxFormBody
    {
        $self = new self();
        $self->addMultiple($fields);

        return $self;
    }

    public function addFile(
        string $fieldName,
        InputFilePath $file,
        string $contentType = 'application/octet-stream'
    ): void {
        $this->isMultipart = true;
        $this->original->addFile($fieldName, $file->path, $contentType);
    }

    public function addField(string $fieldName, float|int|string $value): void
    {
        $this->original->addField($fieldName, (string) $value);
    }

    public function addMultiple(array $fields): void
    {
        foreach ($fields as $key => $value)
        {
            $value instanceof InputFilePath
                ? $this->addFile($key, $value)
                : $this->addField($key, $value);
        }
    }

    public function createBodyStream(): InputStream
    {
        return $this->original->createBodyStream();
    }


    public function headers(): array
    {
        return [
            'Content-Type' => $this->isMultipart
                ? \sprintf('multipart/form-data; boundary=%s', $this->boundary)
                : 'application/x-www-form-urlencoded',
        ];
    }

    public function getBodyLength(): Promise
    {
        /**
         * @noinspection PhpUnnecessaryLocalVariableInspection
         * @noinspection OneTimeUseVariablesInspection
         *
         * @psalm-var    Promise<int> $promise
         */
        $promise = $this->original->getBodyLength();

        return $promise;
    }

    public function preparedBody(): AmpFormBody
    {
        return $this->original;
    }
}
