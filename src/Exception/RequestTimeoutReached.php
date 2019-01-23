<?php

/**
 * PHP Service Bus Http client component
 *
 * @author  Maksim Masiukevich <dev@async-php.com>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace ServiceBus\HttpClient\Exception;

/**
 *
 */
final class RequestTimeoutReached extends \RuntimeException implements HttpClientExceptionMarker
{

}
