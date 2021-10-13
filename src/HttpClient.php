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

use Amp\Promise;

/**
 * Http client interface.
 */
interface HttpClient
{
    /**
     * Execute request.
     *
     * @return Promise<\GuzzleHttp\Psr7\Response>
     *
     * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters
     */
    public function execute(HttpRequest $requestData, ?RequestContext $context = null): Promise;

    /**
     * Download file.
     *
     * @return Promise<string>
     *
     * @throws \ServiceBus\HttpClient\Exception\HttpClientException
     * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters
     */
    public function download(
        string $fileUrl,
        string $destinationDirectory,
        string $fileName,
        ?RequestContext $context = null
    ): Promise;
}
