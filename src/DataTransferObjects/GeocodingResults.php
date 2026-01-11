<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Illuminate\Support\Collection;

/**
 * Contains geocoding results for waypoints specified as addresses.
 */
final readonly class GeocodingResults
{
    /**
     * @param  Collection<int, GeocodedWaypoint>  $intermediates
     */
    public function __construct(
        public ?GeocodedWaypoint $origin = null,
        public ?GeocodedWaypoint $destination = null,
        public Collection $intermediates = new Collection,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $intermediates = collect($data['intermediates'] ?? [])
            ->map(fn (array $waypoint) => GeocodedWaypoint::fromArray($waypoint));

        return new self(
            origin: isset($data['origin']) ? GeocodedWaypoint::fromArray($data['origin']) : null,
            destination: isset($data['destination']) ? GeocodedWaypoint::fromArray($data['destination']) : null,
            intermediates: $intermediates,
        );
    }
}
