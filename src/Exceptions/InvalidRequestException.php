<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Exceptions;

/**
 * Exception thrown when the request parameters are invalid.
 */
class InvalidRequestException extends GoogleRoutesException
{
    public function __construct(string $message = 'Invalid request parameters')
    {
        parent::__construct($message, 400);
    }
}
