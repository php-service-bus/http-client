<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) http client component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\HttpClient;

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
     * @throws \Desperado\ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \Desperado\ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \Desperado\ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \Desperado\ServiceBus\HttpClient\Exception\IncorrectParameters Incorrect request\response data
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
     * @throws \Desperado\ServiceBus\HttpClient\Exception\DnsResolveFailed
     * @throws \Desperado\ServiceBus\HttpClient\Exception\ConnectionFailed
     * @throws \Desperado\ServiceBus\HttpClient\Exception\RequestTimeoutReached
     * @throws \Desperado\ServiceBus\HttpClient\Exception\IncorrectParameters Incorrect request\response data
     */
    public function download(string $filePath, string $destinationDirectory, string $fileName): Promise;
}