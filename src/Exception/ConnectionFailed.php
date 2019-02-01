<?php

/**
 * Abstraction over Http client implementations
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
final class ConnectionFailed extends \RuntimeException implements HttpClientExceptionMarker
{

}
