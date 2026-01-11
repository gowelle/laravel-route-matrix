<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Gowelle\LaravelRouteMatrix\Enums\RouteMatrixElementCondition;

/**
 * Represents a single element in the route matrix response.
 * Contains the travel information between one origin and one destination.
 */
readonly class RouteMatrixElement
{
    public function __construct(
        public int $originIndex,
        public int $destinationIndex,
        public ?int $distanceMeters = null,
        public ?string $duration = null,
        public ?string $staticDuration = null,
        public ?RouteMatrixElementCondition $condition = null,
        public ?array $status = null,
        public ?FallbackInfo $fallbackInfo = null,
        public ?RouteLocalizedValues $localizedValues = null,
    ) {}

    /**
     * Create a RouteMatrixElement from API response array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            originIndex: $data['originIndex'] ?? 0,
            destinationIndex: $data['destinationIndex'] ?? 0,
            distanceMeters: $data['distanceMeters'] ?? null,
            duration: $data['duration'] ?? null,
            staticDuration: $data['staticDuration'] ?? null,
            condition: isset($data['condition'])
                ? RouteMatrixElementCondition::tryFrom($data['condition'])
                : null,
            status: $data['status'] ?? null,
            fallbackInfo: isset($data['fallbackInfo'])
                ? FallbackInfo::fromArray($data['fallbackInfo'])
                : null,
            localizedValues: isset($data['localizedValues'])
                ? RouteLocalizedValues::fromArray($data['localizedValues'])
                : null,
        );
    }

    /**
     * Check if a route was found for this element.
     */
    public function routeExists(): bool
    {
        return $this->condition === RouteMatrixElementCondition::ROUTE_EXISTS;
    }

    /**
     * Get the duration in seconds.
     */
    public function getDurationInSeconds(): ?int
    {
        if ($this->duration === null) {
            return null;
        }

        // Duration is in format "123s"
        return (int) rtrim($this->duration, 's');
    }

    /**
     * Get the static duration in seconds (without traffic).
     */
    public function getStaticDurationInSeconds(): ?int
    {
        if ($this->staticDuration === null) {
            return null;
        }

        return (int) rtrim($this->staticDuration, 's');
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
     * Get a human-readable formatted duration.
     */
    public function getFormattedDuration(): ?string
    {
        $seconds = $this->getDurationInSeconds();

        if ($seconds === null) {
            return null;
        }

        if ($seconds < 60) {
            return "{$seconds} sec";
        }

        $minutes = (int) floor($seconds / 60);
        $hours = (int) floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$remainingMinutes}m";
        }

        return "{$minutes} min";
    }
}
