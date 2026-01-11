<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\ValueObjects;

use JsonSerializable;

/**
 * Preferences for TRANSIT based routes.
 */
final readonly class TransitPreferences implements JsonSerializable
{
    /**
     * @param  array<string>  $allowedTravelModes  Allowed transit travel modes (BUS, SUBWAY, TRAIN, LIGHT_RAIL, RAIL)
     * @param  string|null  $routingPreference  Preference for transit routing (LESS_WALKING, FEWER_TRANSFERS)
     */
    public function __construct(
        public array $allowedTravelModes = [],
        public ?string $routingPreference = null,
    ) {}

    /**
     * Create with only allowed travel modes.
     *
     * @param  array<string>  $modes
     */
    public static function withModes(array $modes): self
    {
        return new self(allowedTravelModes: $modes);
    }

    /**
     * Create with less walking preference.
     */
    public static function lessWalking(): self
    {
        return new self(routingPreference: 'LESS_WALKING');
    }

    /**
     * Create with fewer transfers preference.
     */
    public static function fewerTransfers(): self
    {
        return new self(routingPreference: 'FEWER_TRANSFERS');
    }

    public function toArray(): array
    {
        $data = [];

        if (! empty($this->allowedTravelModes)) {
            $data['allowedTravelModes'] = $this->allowedTravelModes;
        }

        if ($this->routingPreference !== null) {
            $data['routingPreference'] = $this->routingPreference;
        }

        return $data;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
