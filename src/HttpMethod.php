<?php

declare(strict_types=1);

namespace ServiceBus\HttpClient;

enum HttpMethod
{
    case GET;
    case HEAD;
    case POST;
    case PUT;
    case DELETE;
    case CONNECT;
    case OPTIONS;
    case TRACE;
    case PATCH;
}
