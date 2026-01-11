<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

/**
 * Contains geocoding information for an origin, destination, or intermediate waypoint.
 */
final readonly class GeocodedWaypoint
{
    /**
     * @param  array<int, string>  $placeTypes
     */
    public function __construct(
        public ?string $placeId = null,
        public array $placeTypes = [],
        public ?float $latitude = null,
        public ?float $longitude = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $location = $data['location']['latLng'] ?? [];

        return new self(
            placeId: $data['placeId'] ?? null,
            placeTypes: $data['type'] ?? [],
            latitude: $location['latitude'] ?? null,
            longitude: $location['longitude'] ?? null,
        );
    }
}
