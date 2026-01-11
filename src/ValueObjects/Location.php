<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\ValueObjects;

use JsonSerializable;

/**
 * Represents a location wrapper for LatLng coordinates.
 */
final readonly class Location implements JsonSerializable
{
    public function __construct(
        public LatLng $latLng,
    ) {}

    /**
     * Create a Location from LatLng coordinates.
     */
    public static function fromLatLng(float $latitude, float $longitude): self
    {
        return new self(new LatLng($latitude, $longitude));
    }

    /**
     * Create a Location from an array.
     *
     * @param  array{lat?: float, latitude?: float, lng?: float, longitude?: float}  $coordinates
     */
    public static function fromArray(array $coordinates): self
    {
        return new self(LatLng::fromArray($coordinates));
    }

    public function toArray(): array
    {
        return [
            'latLng' => $this->latLng->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
