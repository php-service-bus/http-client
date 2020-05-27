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
 * Http request options
 *
 * @psalm-readonly
 */
final class RequestContext
{
    /** @var int */
    public $tcpConnectTimeout;

    /** @var int */
    public $tlsHandshakeTimeout;

    /** @var int */
    public $transferTimeout;

    /** @var bool */
    public $logRequest;

    /** @var bool */
    public $logResponse;

    /** @var string */
    public $traceId;

    /** @var string|null */
    public $protocolVersion;

    public function __construct(
        ?string $traceId = null,
        int $tcpConnectTimeout = 10000,
        int $tlsHandshakeTimeout = 10000,
        int $transferTimeout = 10000,
        bool $logRequest = true,
        bool $logResponse = true,
        ?string $protocolVersion = null
    ) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->traceId             = $traceId ?? \sha1(\random_bytes(32));
        $this->tcpConnectTimeout   = $tcpConnectTimeout;
        $this->tlsHandshakeTimeout = $tlsHandshakeTimeout;
        $this->transferTimeout     = $transferTimeout;
        $this->logRequest          = $logRequest;
        $this->logResponse         = $logResponse;
        $this->protocolVersion     = $protocolVersion;
    }
}
