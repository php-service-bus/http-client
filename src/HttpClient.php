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

use Amp\Promise;

/**
 * Http client interface.
 */
interface HttpClient
{
    /**
     * Execute request.
     *
     * @param HttpRequest $requestData
     *
     * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters Incorrect request\response data
     *
     * @return Promise<\GuzzleHttp\Psr7\Response>
     *
     */
    public function execute(HttpRequest $requestData): Promise;

    /**
     * Download file.
     *
     * @param string $filePath
     * @param string $destinationDirectory
     * @param string $fileName
     *
     * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters Incorrect request\response data
     *
     * @return Promise<string> Path to file
     *
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName): Promise;
}
