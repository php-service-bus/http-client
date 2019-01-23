<?php

/**
 * PHP Service Bus Http client component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient;

/**
 * Http request data
 *
 * @property-read string                          $method
 * @property-read string                          $url
 * @property-read array<string, string|int|float> $headers
 * @property-read FormBody|string|null            $body
 */
class HttpRequest
{
    /**
     * Http method
     *
     * @var string
     */
    public $method;

    /**
     * Request URL
     *
     * @var string
     */
    public $url;

    /**
     * Request headers
     *
     * @var array<string, string|int|float>
     */
    public $headers;

    /**
     * Request payload
     *
     * @var FormBody|string|null
     */
    public $body;

    /**
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
     * @param string               $method
     * @param string               $url
     * @param array                $headers
     * @param FormBody|string|null $body
     */
    private function __construct(string $method, string $url, array $headers = [], $body = null)
    {
        if($body instanceof FormBody)
        {
            /** @var array<string, string|int|float> $headers */
            $headers = \array_merge($body->headers(), $headers);
        }

        $this->method  = $method;
        $this->url     = $url;
        $this->headers = $headers;
        $this->body    = $body;
    }

    /**
     * Is POST request
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return 'POST' === $this->method;
    }
}
