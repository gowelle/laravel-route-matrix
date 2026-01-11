<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

/**
 * Represents an encoded polyline.
 */
final readonly class Polyline
{
    public function __construct(
        public ?string $encodedPolyline = null,
        public ?array $geoJsonLinestring = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            encodedPolyline: $data['encodedPolyline'] ?? null,
            geoJsonLinestring: $data['geoJsonLinestring'] ?? null,
        );
    }

    /**
     * Check if the polyline is encoded format.
     */
    public function isEncoded(): bool
    {
        return $this->encodedPolyline !== null;
    }

    /**
     * Check if the polyline is GeoJSON format.
     */
    public function isGeoJson(): bool
    {
        return $this->geoJsonLinestring !== null;
    }
}
