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

use Amp\Promise;

/**
 * Http client interface
 */
interface HttpClient
{
    /**
     * Execute request
     *
     * @param HttpRequest $requestData
     *
     * @return Promise<\GuzzleHttp\Psr7\Response>
     *
     * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters Incorrect request\response data
     */
    public function execute(HttpRequest $requestData): Promise;

    /**
     * Download file
     *
     * @param string $filePath
     * @param string $destinationDirectory
     * @param string $fileName
     *
     * @return Promise<string> Path to file
     *
     * @throws \ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \ServiceBus\HttpClient\Exception\IncorrectParameters Incorrect request\response data
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName): Promise;
}