<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\ValueObjects;

use JsonSerializable;

/**
 * Encapsulates a set of optional conditions to satisfy when calculating routes.
 */
final readonly class RouteModifiers implements JsonSerializable
{
    public function __construct(
        public bool $avoidTolls = false,
        public bool $avoidHighways = false,
        public bool $avoidFerries = false,
        public bool $avoidIndoor = false,
    ) {}

    /**
     * Create RouteModifiers from an array of options.
     *
     * @param array{
     *     avoidTolls?: bool,
     *     avoidHighways?: bool,
     *     avoidFerries?: bool,
     *     avoidIndoor?: bool,
     * } $options
     */
    public static function fromArray(array $options): self
    {
        return new self(
            avoidTolls: $options['avoidTolls'] ?? false,
            avoidHighways: $options['avoidHighways'] ?? false,
            avoidFerries: $options['avoidFerries'] ?? false,
            avoidIndoor: $options['avoidIndoor'] ?? false,
        );
    }

    /**
     * Check if any modifiers are set.
     */
    public function hasModifiers(): bool
    {
        return $this->avoidTolls
            || $this->avoidHighways
            || $this->avoidFerries
            || $this->avoidIndoor;
    }

    public function toArray(): array
    {
        return [
            'avoidTolls' => $this->avoidTolls,
            'avoidHighways' => $this->avoidHighways,
            'avoidFerries' => $this->avoidFerries,
            'avoidIndoor' => $this->avoidIndoor,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
