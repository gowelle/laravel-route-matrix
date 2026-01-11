<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Exceptions;

use Exception;

/**
 * Base exception for Google Routes API errors.
 */
class GoogleRoutesException extends Exception
{
    /**
     * Create a new exception from an API error response.
     */
    public static function fromApiError(array $error, int $statusCode): self
    {
        $message = $error['message'] ?? 'Unknown error occurred';
        $code = $error['code'] ?? $statusCode;

        return new self($message, $code);
    }
}
