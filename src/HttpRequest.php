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

/**
 * Http request data.
 */
class HttpRequest
{
    /**
     * Http method.
     *
     * @psalm-readonly
     *
     * @var string
     */
    public $method;

    /**
     * Request URL.
     *
     * @psalm-readonly
     *
     * @var string
     */
    public $url;

    /**
     * Request headers.
     *
     * @psalm-readonly
     * @psalm-var array<string, array|string>
     *
     * @var array
     */
    public $headers;

    /**
     * Request payload.
     *
     * @psalm-readonly
     *
     * @var FormBody|string|null
     */
    public $body;

    /**
     * @psalm-param array<string, string|int|float> $queryParameters
     * @psalm-param array<string, array|string> $headers
     */
    public static function get(string $url, array $queryParameters = [], array $headers = []): self
    {
        if (\count($queryParameters) !== 0)
        {
            $url = \sprintf('%s?%s', \rtrim($url, '?'), \http_build_query($queryParameters));
        }

        return new self('GET', \rtrim($url, '?'), $headers);
    }

    /**
     * @psalm-param array<string, string|array> $headers
     */
    public static function post(string $url, FormBody|string $body, array $headers = []): self
    {
        return new self('POST', $url, $headers, $body);
    }

    /**
     * @psalm-param array<string, string|array> $headers
     */
    public function __construct(string $method, string $url, array $headers = [], FormBody|string|null $body = null)
    {
        if ($body instanceof FormBody)
        {
            $headers = \array_merge($body->headers(), $headers);
        }

        $this->method  = $method;
        $this->url     = $url;
        $this->headers = $headers;
        $this->body    = $body;
    }

    /**
     * Is POST request.
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }
}
