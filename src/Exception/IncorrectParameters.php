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
 * Incorrect request\response data.
 */
final class IncorrectParameters extends \InvalidArgumentException implements HttpClientExceptionMarker
{
}
