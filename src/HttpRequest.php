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
 */
class HttpRequest
{
    /**
     * Http method.
     *
     * @readonly
     *
     * @var string
     */
    public $method;

    /**
     * Request URL.
     *
     * @readonly
     *
     * @var string
     */
    public $url;

    /**
     * Request headers.
     *
     * @readonly
     *
     * @psalm-var array<string, array|string>
     *
     * @var array
     */
    public $headers;

    /**
     * Request payload.
     *
     * @readonly
     *
     * @var FormBody|string|null
     */
    public $body;

    /**
     * @psalm-param array<string, string|int|float> $queryParameters
     * @psalm-param array<string, array|string> $headers
     *
     * @param string $url
     * @param array  $queryParameters
     * @param array  $headers
     *
     * @return self
     */
    public static function get(string $url, array $queryParameters = [], array $headers = []): self
    {
        $url = \sprintf('%s?%s', \rtrim($url, '?'), \http_build_query($queryParameters));

        return new self('GET', \rtrim($url, '?'), $headers);
    }

    /**
     * @psalm-param array<string, string|array> $headers
     *
     * @param string          $url
     * @param FormBody|string $body
     * @param array           $headers
     *
     * @return self
     */
    public static function post(string $url, $body, array $headers = []): self
    {
        return new self('POST', $url, $headers, $body);
    }

    /**
     * @psalm-param array<string, string|array> $headers
     *
     * @param string               $method
     * @param string               $url
     * @param array                $headers
     * @param FormBody|string|null $body
     */
    private function __construct(string $method, string $url, array $headers = [], $body = null)
    {
        if($body instanceof FormBody)
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
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return 'POST' === $this->method;
    }
}
