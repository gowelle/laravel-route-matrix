<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Illuminate\Support\Collection;

/**
 * Represents a computed route.
 */
final readonly class Route
{
    /**
     * @param  Collection<int, RouteLeg>  $legs
     * @param  array<int, string>  $routeLabels
     * @param  array<int, string>  $warnings
     * @param  array<int, int>  $optimizedIntermediateWaypointIndex
     */
    public function __construct(
        public ?int $distanceMeters = null,
        public ?string $duration = null,
        public ?string $staticDuration = null,
        public ?Polyline $polyline = null,
        public ?string $description = null,
        public Collection $legs = new Collection,
        public ?Viewport $viewport = null,
        public ?RouteLocalizedValues $localizedValues = null,
        public ?string $routeToken = null,
        public array $routeLabels = [],
        public array $warnings = [],
        public array $optimizedIntermediateWaypointIndex = [],
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $legs = collect($data['legs'] ?? [])
            ->map(fn (array $leg) => RouteLeg::fromArray($leg));

        return new self(
            distanceMeters: $data['distanceMeters'] ?? null,
            duration: $data['duration'] ?? null,
            staticDuration: $data['staticDuration'] ?? null,
            polyline: isset($data['polyline']) ? Polyline::fromArray($data['polyline']) : null,
            description: $data['description'] ?? null,
            legs: $legs,
            viewport: isset($data['viewport']) ? Viewport::fromArray($data['viewport']) : null,
            localizedValues: isset($data['localizedValues']) ? RouteLocalizedValues::fromArray($data['localizedValues']) : null,
            routeToken: $data['routeToken'] ?? null,
            routeLabels: $data['routeLabels'] ?? [],
            warnings: $data['warnings'] ?? [],
            optimizedIntermediateWaypointIndex: $data['optimizedIntermediateWaypointIndex'] ?? [],
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

    /**
     * Get the duration as a human-readable string.
     */
    public function getFormattedDuration(): ?string
    {
        $seconds = $this->getDurationInSeconds();
        if ($seconds === null) {
            return null;
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes} min";
    }

    /**
     * Check if this is the default recommended route.
     */
    public function isDefaultRoute(): bool
    {
        return in_array('DEFAULT_ROUTE', $this->routeLabels);
    }

    /**
     * Check if this is a fuel-efficient route.
     */
    public function isFuelEfficient(): bool
    {
        return in_array('FUEL_EFFICIENT', $this->routeLabels);
    }
}
