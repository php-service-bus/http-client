<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=0);

namespace ServiceBus\HttpClient\Result;

/**
 * @psalm-immutable
 */
final class Success implements Either
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
     * @var string
     */
    public $requestPayload;

    /**
     * @psalm-readonly
     *
     * @var string
     */
    public $responseBody;

    /**
     * @psalm-readonly
     *
     * @var string
     */
    public $description;

    public function __construct(int $resultCode, string $requestPayload, string $responseBody, string $description)
    {
        $this->resultCode     = $resultCode;
        $this->requestPayload = $requestPayload;
        $this->responseBody   = $responseBody;
        $this->description    = $description;
    }
}
