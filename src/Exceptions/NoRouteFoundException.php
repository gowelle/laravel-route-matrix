<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Exceptions;

/**
 * Exception thrown when no route can be found between the specified waypoints.
 */
class NoRouteFoundException extends GoogleRoutesException
{
    public function __construct(string $message = 'No route could be found between the specified waypoints')
    {
        parent::__construct($message, 404);
    }
}
