<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\ValueObjects;

use JsonSerializable;

/**
 * Represents a waypoint that can be a location, place ID, or address.
 *
 * A waypoint defines a point along the route. Origin and destination are
 * required waypoints, while intermediate waypoints are optional.
 */
final readonly class Waypoint implements JsonSerializable
{
    private function __construct(
        public ?Location $location = null,
        public ?string $placeId = null,
        public ?string $address = null,
        public bool $via = false,
        public bool $vehicleStopover = false,
        public bool $sideOfRoad = false,
    ) {}

    /**
     * Create a waypoint from latitude/longitude coordinates.
     */
    public static function fromLatLng(
        float $latitude,
        float $longitude,
        bool $via = false,
        bool $vehicleStopover = false,
        bool $sideOfRoad = false,
    ): self {
        return new self(
            location: Location::fromLatLng($latitude, $longitude),
            via: $via,
            vehicleStopover: $vehicleStopover,
            sideOfRoad: $sideOfRoad,
        );
    }

    /**
     * Create a waypoint from an array of coordinates.
     *
     * @param  array{lat?: float, latitude?: float, lng?: float, longitude?: float}  $coordinates
     */
    public static function fromArray(
        array $coordinates,
        bool $via = false,
        bool $vehicleStopover = false,
        bool $sideOfRoad = false,
    ): self {
        return new self(
            location: Location::fromArray($coordinates),
            via: $via,
            vehicleStopover: $vehicleStopover,
            sideOfRoad: $sideOfRoad,
        );
    }

    /**
     * Create a waypoint from a Google Place ID.
     */
    public static function fromPlaceId(
        string $placeId,
        bool $via = false,
        bool $vehicleStopover = false,
        bool $sideOfRoad = false,
    ): self {
        return new self(
            placeId: $placeId,
            via: $via,
            vehicleStopover: $vehicleStopover,
            sideOfRoad: $sideOfRoad,
        );
    }

    /**
     * Create a waypoint from an address string.
     */
    public static function fromAddress(
        string $address,
        bool $via = false,
        bool $vehicleStopover = false,
        bool $sideOfRoad = false,
    ): self {
        return new self(
            address: $address,
            via: $via,
            vehicleStopover: $vehicleStopover,
            sideOfRoad: $sideOfRoad,
        );
    }

    /**
     * Convert to API request format.
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->location !== null) {
            $data['location'] = $this->location->toArray();
        } elseif ($this->placeId !== null) {
            $data['placeId'] = $this->placeId;
        } elseif ($this->address !== null) {
            $data['address'] = $this->address;
        }

        if ($this->via) {
            $data['via'] = true;
        }

        if ($this->vehicleStopover) {
            $data['vehicleStopover'] = true;
        }

        if ($this->sideOfRoad) {
            $data['sideOfRoad'] = true;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
