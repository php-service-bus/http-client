<?php

/**
 * Abstraction over Http client implementations.
 *
 * @author  Maksim Masiukevich <contacts@desperado.dev>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 0);

namespace ServiceBus\HttpClient\Exception;

/**
 * Another exception occurred.
 */
class HttpClientException extends \RuntimeException implements HttpClientExceptionMarker
{
}
