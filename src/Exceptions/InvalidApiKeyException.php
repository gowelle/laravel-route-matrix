<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Exceptions;

/**
 * Exception thrown when the API key is missing or invalid.
 */
class InvalidApiKeyException extends GoogleRoutesException
{
    public function __construct(string $message = 'Invalid or missing Google Routes API key')
    {
        parent::__construct($message, 401);
    }
}
