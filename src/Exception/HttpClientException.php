<?php

/**
 * PHP Service Bus (publish-subscribe pattern implementation) http client component
 *
 * @author  Maksim Masiukevich <desperado@minsk-info.ru>
 * @license MIT
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types = 1);

namespace Desperado\ServiceBus\HttpClient\Exception;

/**
 * Another exception occured
 */
class HttpClientException extends \RuntimeException implements HttpClientExceptionMarker
{

}
