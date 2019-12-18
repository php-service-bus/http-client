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
 * Http request data.
 *
 * @psalm-readonly
 */
class HttpRequest
{
    /**
     * Http method.
     *
     * @var string
     */
    public $method;

    /**
     * Request URL.
     *
     * @var string
     */
    public $url;

    /**
     * Request headers.
     *
     * @psalm-var array<string, array|string>
     *
     * @var array
     */
    public $headers;

    /**
     * Request payload.
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
        if (0 !== \count($queryParameters))
        {
            $url = \sprintf('%s?%s', \rtrim($url, '?'), \http_build_query($queryParameters));
        }

        return new self('GET', \rtrim($url, '?'), $headers);
    }

    /**
     * @psalm-param array<string, string|array> $headers
     *
     * @param FormBody|string $body
     */
    public static function post(string $url, $body, array $headers = []): self
    {
        return new self('POST', $url, $headers, $body);
    }

    /**
     * @psalm-param array<string, string|array> $headers
     *
     * @param FormBody|string|null $body
     */
    public function __construct(string $method, string $url, array $headers = [], $body = null)
    {
        if ($body instanceof FormBody)
        {
            /** @psalm-var array<string, string|array> $headers */
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
        return 'POST' === $this->method;
    }
}
