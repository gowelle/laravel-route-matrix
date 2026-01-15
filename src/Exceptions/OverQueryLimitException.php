<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Exceptions;

/**
 * Exception thrown when the API quota has been exceeded (HTTP 429).
 */
class OverQueryLimitException extends GoogleRoutesException
{
    //
}
