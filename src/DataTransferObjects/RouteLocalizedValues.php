<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

/**
 * Contains text representations of route properties.
 */
final readonly class RouteLocalizedValues
{
    public function __construct(
        public ?string $distance = null,
        public ?string $duration = null,
        public ?string $staticDuration = null,
        public ?string $transitFare = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            distance: $data['distance']['text'] ?? null,
            duration: $data['duration']['text'] ?? null,
            staticDuration: $data['staticDuration']['text'] ?? null,
            transitFare: $data['transitFare']['text'] ?? null,
        );
    }
}
