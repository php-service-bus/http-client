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
     * @var HttpMethod
     */
    public $method;

    /**
     * Request URL.
     *
     * @psalm-readonly
     * @psalm-var non-empty-string
     *
     * @var string
     */
    public $url;

    /**
     * Request headers.
     *
     * @psalm-readonly
     * @psalm-var array<non-empty-string, array|string>
     *
     * @var array
     */
    public $headers;

    /**
     * Request payload.
     *
     * @psalm-readonly
     *
     * @var FormBody|non-empty-string|null
     */
    public $body;

    /**
     * @psalm-param non-empty-string                          $url
     * @psalm-param array<non-empty-string, string|int|float> $queryParameters
     * @psalm-param array<non-empty-string, array|string>     $headers
     */
    public static function get(string $url, array $queryParameters = [], array $headers = []): self
    {
        return new self(HttpMethod::GET, self::buildUrl($url, $queryParameters), $headers);
    }

    /**
     * @psalm-param non-empty-string                          $url
     * @psalm-param array<non-empty-string, string|int|float> $queryParameters
     */
    public static function delete(string $url, array $queryParameters = []): self
    {
        return new self(HttpMethod::DELETE, self::buildUrl($url, $queryParameters));
    }

    /**
     * @psalm-param non-empty-string                      $url
     * @psalm-param FormBody|non-empty-string|null        $body
     * @psalm-param array<non-empty-string, string|array> $headers
     */
    public static function post(string $url, FormBody|string|null $body, array $headers = []): self
    {
        return new self(HttpMethod::POST, $url, $headers, $body);
    }

    /**
     * @psalm-param non-empty-string                      $url
     * @psalm-param FormBody|non-empty-string|null        $body
     * @psalm-param array<non-empty-string, string|array> $headers
     */
    public static function put(string $url, FormBody|string|null $body, array $headers = []): self
    {
        return new self(HttpMethod::PUT, $url, $headers, $body);
    }

    /**
     * @psalm-param non-empty-string                      $url
     * @psalm-param FormBody|non-empty-string|null        $body
     * @psalm-param array<non-empty-string, string|array> $headers
     */
    public static function patch(string $url, FormBody|string|null $body, array $headers = []): self
    {
        return new self(HttpMethod::PATCH, $url, $headers, $body);
    }

    /**
     * @psalm-param non-empty-string                      $url
     * @psalm-param array<non-empty-string, array|string> $headers
     * @psalm-param FormBody|non-empty-string|null        $body
     */
    public function __construct(HttpMethod $method, string $url, array $headers = [], FormBody|string|null $body = null)
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
     * @psalm-param non-empty-string                          $url
     * @psalm-param array<non-empty-string, string|int|float> $queryParameters
     *
     * @psalm-return non-empty-string
     */
    private static function buildUrl(string $url, array $queryParameters): string
    {
        if (\count($queryParameters) !== 0)
        {
            return \sprintf('%s?%s', \rtrim($url, '?'), \http_build_query($queryParameters));
        }

        return $url;
    }
}
