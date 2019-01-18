<?php

/**
 * PHP Service Bus (publish-subscribe pattern) Http client component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Exception;

/**
 * Another exception occured
 */
class HttpClientException extends \RuntimeException implements HttpClientExceptionMarker
{

}
