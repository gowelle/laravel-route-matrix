<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\ValueObjects;

use JsonSerializable;

/**
 * Represents a geographic coordinate with latitude and longitude.
 */
final readonly class LatLng implements JsonSerializable
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
        if ($latitude < -90 || $latitude > 90) {
            throw new \InvalidArgumentException(
                "Latitude must be between -90 and 90, got {$latitude}"
            );
        }

        if ($longitude < -180 || $longitude > 180) {
            throw new \InvalidArgumentException(
                "Longitude must be between -180 and 180, got {$longitude}"
            );
        }
    }

    /**
     * Create a LatLng from an array.
     *
     * @param  array{lat?: float, latitude?: float, lng?: float, longitude?: float}  $coordinates
     */
    public static function fromArray(array $coordinates): self
    {
        $lat = $coordinates['lat'] ?? $coordinates['latitude'] ?? null;
        $lng = $coordinates['lng'] ?? $coordinates['longitude'] ?? null;

        if ($lat === null || $lng === null) {
            throw new \InvalidArgumentException(
                'Array must contain lat/latitude and lng/longitude keys'
            );
        }

        return new self((float) $lat, (float) $lng);
    }

    /**
     * Create a LatLng from a comma-separated string.
     *
     * @param  string  $coordinates  e.g., "37.419734,-122.0827784"
     */
    public static function fromString(string $coordinates): self
    {
        $parts = explode(',', $coordinates);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException(
                'String must be in format "latitude,longitude"'
            );
        }

        return new self((float) trim($parts[0]), (float) trim($parts[1]));
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return "{$this->latitude},{$this->longitude}";
    }
}
