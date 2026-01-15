<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Illuminate\Support\Collection;

/**
 * Represents a segment of a route between two non-via waypoints.
 */
final readonly class RouteLeg
{
    /**
     * @param  Collection<int, RouteLegStep>  $steps
     */
    public function __construct(
        public ?int $distanceMeters = null,
        public ?string $duration = null,
        public ?string $staticDuration = null,
        public ?Polyline $polyline = null,
        public ?\Gowelle\LaravelRouteMatrix\ValueObjects\LatLng $startLocation = null,
        public ?\Gowelle\LaravelRouteMatrix\ValueObjects\LatLng $endLocation = null,
        public Collection $steps = new Collection,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $steps = collect($data['steps'] ?? [])
            ->map(fn (array $step) => RouteLegStep::fromArray($step));

        return new self(
            distanceMeters: $data['distanceMeters'] ?? null,
            duration: $data['duration'] ?? null,
            staticDuration: $data['staticDuration'] ?? null,
            polyline: isset($data['polyline']) ? Polyline::fromArray($data['polyline']) : null,
            startLocation: isset($data['startLocation']['latLng'])
                ? \Gowelle\LaravelRouteMatrix\ValueObjects\LatLng::fromArray($data['startLocation']['latLng'])
                : null,
            endLocation: isset($data['endLocation']['latLng'])
                ? \Gowelle\LaravelRouteMatrix\ValueObjects\LatLng::fromArray($data['endLocation']['latLng'])
                : null,
            steps: $steps,
        );
    }

    /**
     * Get the duration in seconds.
     */
    public function getDurationInSeconds(): ?int
    {
        if ($this->duration === null) {
            return null;
        }

        // Duration is in format "165s"
        return (int) rtrim($this->duration, 's');
    }

    /**
     * Get the distance in kilometers.
     */
    public function getDistanceInKilometers(): ?float
    {
        if ($this->distanceMeters === null) {
            return null;
        }

        return $this->distanceMeters / 1000;
    }

    /**
     * Get the distance in miles.
     */
    public function getDistanceInMiles(): ?float
    {
        if ($this->distanceMeters === null) {
            return null;
        }

        return $this->distanceMeters / 1609.344;
    }
}
