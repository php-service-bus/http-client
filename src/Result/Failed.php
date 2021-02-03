<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 0);

namespace ServiceBus\HttpClient\Result;

/**
 * @psalm-immutable
 */
final class Failed implements Either
{
    /**
     * @psalm-readonly
     *
     * @var int
     */
    public $resultCode;

    /**
     * @psalm-readonly
     *
     * @var string|null
     */
    public $requestPayload;

    /**
     * @psalm-readonly
     *
     * @var string|null
     */
    public $responseBody;

    /**
     * This is an internal request related error.
     *
     * @psalm-readonly
     *
     * @var bool
     */
    public $isInternalError = false;

    /**
     * This is a server side request processing error.
     *
     * @psalm-readonly
     *
     * @var bool
     */
    public $isServerError = false;

    /**
     * This is an error associated with invalid request parameters.
     *
     * @psalm-readonly
     *
     * @var bool
     */
    public $isClientError = false;

    /**
     * @psalm-readonly
     *
     * @var string
     */
    public $description;

    /**
     * This is an error associated with invalid request parameters.
     */
    public static function client(int $resultCode, string $requestPayload, string $responseBody, string $description): self
    {
        return new self(
            isInternalError: false,
            isClientError: true,
            isServerError: false,
            description: $description,
            resultCode: $resultCode,
            requestPayload: $requestPayload,
            responseBody: $responseBody
        );
    }

    /**
     * This is a server side request processing error.
     */
    public static function server(int $resultCode, string $requestPayload, string $responseBody, string $description): self
    {
        return new self(
            isInternalError: false,
            isClientError: false,
            isServerError: true,
            description: $description,
            resultCode: $resultCode,
            requestPayload: $requestPayload,
            responseBody: $responseBody
        );
    }

    /**
     * This is an internal request related error.
     */
    public static function internal(string $description): self
    {
        return new self(
            isInternalError: true,
            isClientError: false,
            isServerError: false,
            description: $description,
            resultCode: 400,
            requestPayload: null,
            responseBody: null
        );
    }

    private function __construct(
        bool $isInternalError,
        bool $isClientError,
        bool $isServerError,
        string $description,
        int $resultCode,
        ?string $requestPayload,
        ?string $responseBody
    ) {
        $this->isInternalError = $isInternalError;
        $this->isClientError   = $isClientError;
        $this->isServerError   = $isServerError;
        $this->description     = $description;
        $this->resultCode      = $resultCode;
        $this->requestPayload  = $requestPayload;
        $this->responseBody    = $responseBody;
    }
}
