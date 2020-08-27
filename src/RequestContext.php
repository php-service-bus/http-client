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

use function ServiceBus\Common\uuid;

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

    /** @var int */
    public $inactivityTimeout;

    /** @var bool */
    public $logRequest;

    /** @var bool */
    public $logResponse;

    /** @var string */
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
            null,
            $tcpConnectTimeout,
            $tlsHandshakeTimeout,
            $transferTimeout,
            $inactivityTimeout,
            false,
            false
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
