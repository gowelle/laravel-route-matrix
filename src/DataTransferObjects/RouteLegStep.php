<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\DataTransferObjects;

use Gowelle\LaravelRouteMatrix\Enums\Maneuver;
use Gowelle\LaravelRouteMatrix\ValueObjects\LatLng;

/**
 * Represents a step within a route leg.
 */
final readonly class RouteLegStep
{
    public function __construct(
        public ?int $distanceMeters = null,
        public ?string $duration = null,
        public ?string $staticDuration = null,
        public ?Polyline $polyline = null,
        public ?LatLng $startLocation = null,
        public ?LatLng $endLocation = null,
        public ?string $instructions = null,
        public ?Maneuver $maneuver = null,
    ) {}

    /**
     * Create from API response data.
     */
    public static function fromArray(array $data): self
    {
        $maneuver = null;
        if (isset($data['navigationInstruction']['maneuver'])) {
            $maneuver = Maneuver::tryFrom($data['navigationInstruction']['maneuver']);
        }

        return new self(
            distanceMeters: $data['distanceMeters'] ?? null,
            duration: $data['duration'] ?? null,
            staticDuration: $data['staticDuration'] ?? null,
            polyline: isset($data['polyline']) ? Polyline::fromArray($data['polyline']) : null,
            startLocation: isset($data['startLocation']['latLng'])
                ? LatLng::fromArray($data['startLocation']['latLng'])
                : null,
            endLocation: isset($data['endLocation']['latLng'])
                ? LatLng::fromArray($data['endLocation']['latLng'])
                : null,
            instructions: $data['navigationInstruction']['instructions'] ?? null,
            maneuver: $maneuver,
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
}
