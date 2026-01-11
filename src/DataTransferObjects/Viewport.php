<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

/**
 * Represents a viewport (bounding box) for a route.
 */
final readonly class Viewport
{
    public function __construct(
        public ?float $lowLatitude = null,
        public ?float $lowLongitude = null,
        public ?float $highLatitude = null,
        public ?float $highLongitude = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $low = $data['low'] ?? [];
        $high = $data['high'] ?? [];

        return new self(
            lowLatitude: $low['latitude'] ?? null,
            lowLongitude: $low['longitude'] ?? null,
            highLatitude: $high['latitude'] ?? null,
            highLongitude: $high['longitude'] ?? null,
        );
    }

    /**
     * Get as array in Google Maps format.
     */
    public function toBounds(): array
    {
        return [
            'southwest' => [
                'lat' => $this->lowLatitude,
                'lng' => $this->lowLongitude,
            ],
            'northeast' => [
                'lat' => $this->highLatitude,
                'lng' => $this->highLongitude,
            ],
        ];
    }
}
