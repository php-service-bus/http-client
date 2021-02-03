<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 0);

namespace ServiceBus\HttpClient;

use function ServiceBus\Common\uuid;

/**
 * Http request options
 */
final class RequestContext
{
    /**
     * @psalm-readonly
     *
     * @var int
     */
    public $tcpConnectTimeout;

    /**
     * @psalm-readonly
     *
     * @var int
     */
    public $tlsHandshakeTimeout;

    /**
     * @psalm-readonly
     *
     * @var int
     */
    public $transferTimeout;

    /**
     * @psalm-readonly
     *
     * @var int
     */
    public $inactivityTimeout;

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $logRequest;

    /**
     * @psalm-readonly
     *
     * @var bool
     */
    public $logResponse;

    /**
     * @psalm-readonly
     *
     * @var string
     */
    public $traceId;

    /** @var string|null */
    public $protocolVersion;

    public static function withoutLogging(
        int $tcpConnectTimeout = 15000,
        int $tlsHandshakeTimeout = 15000,
        int $transferTimeout = 15000,
        int $inactivityTimeout = 15000
    ): self {
        return new self(
            traceId: null,
            tcpConnectTimeout: $tcpConnectTimeout,
            tlsHandshakeTimeout: $tlsHandshakeTimeout,
            transferTimeout: $transferTimeout,
            inactivityTimeout: $inactivityTimeout,
            logRequest: false,
            logResponse: false,
            protocolVersion: null
        );
    }

    public function __construct(
        ?string $traceId = null,
        int $tcpConnectTimeout = 15000,
        int $tlsHandshakeTimeout = 15000,
        int $transferTimeout = 15000,
        int $inactivityTimeout = 15000,
        bool $logRequest = true,
        bool $logResponse = true,
        ?string $protocolVersion = null
    ) {
        $this->traceId             = $traceId ?? uuid();
        $this->tcpConnectTimeout   = $tcpConnectTimeout;
        $this->tlsHandshakeTimeout = $tlsHandshakeTimeout;
        $this->transferTimeout     = $transferTimeout;
        $this->logRequest          = $logRequest;
        $this->logResponse         = $logResponse;
        $this->protocolVersion     = $protocolVersion;
        $this->inactivityTimeout   = $inactivityTimeout;
    }
}
