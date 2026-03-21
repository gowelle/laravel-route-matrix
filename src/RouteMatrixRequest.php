<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix;

use DateTimeInterface;
use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixResponse;
use Gowelle\LaravelRouteMatrix\Enums\ExtraComputation;
use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Gowelle\LaravelRouteMatrix\Enums\TrafficModel;
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;
use Gowelle\LaravelRouteMatrix\Enums\Units;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\ValueObjects\RouteModifiers;
use Gowelle\LaravelRouteMatrix\ValueObjects\TransitPreferences;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

/**
 * Fluent builder for constructing route matrix requests.
 */
class RouteMatrixRequest
{
    /** @var array<int, array{waypoint: Waypoint, routeModifiers: ?RouteModifiers}> */
    private array $origins = [];

    /** @var array<int, Waypoint> */
    private array $destinations = [];

    private ?TravelMode $travelMode = null;

    private ?RoutingPreference $routingPreference = null;

    private ?DateTimeInterface $departureTime = null;

    private ?DateTimeInterface $arrivalTime = null;

    private ?string $languageCode = null;

    private ?string $regionCode = null;

    private ?Units $units = null;

    /** @var array<int, ExtraComputation> */
    private array $extraComputations = [];

    private ?TrafficModel $trafficModel = null;

    private ?TransitPreferences $transitPreferences = null;

    private ?GoogleRoutesClientInterface $client = null;

    /**
     * Create a new RouteMatrixRequest with optional client.
     */
    public function __construct(?GoogleRoutesClientInterface $client = null)
    {
        $this->client = $client;
    }

    /**
     * Add an origin waypoint.
     *
     * @param  Waypoint|array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $origin
     */
    public function addOrigin(
        Waypoint|array|string $origin,
        ?RouteModifiers $routeModifiers = null
    ): self {
        $this->origins[] = [
            'waypoint' => $this->resolveWaypoint($origin),
            'routeModifiers' => $routeModifiers,
        ];

        return $this;
    }

    /**
     * Set multiple origins at once.
     *
     * @param  array<int, Waypoint|array|string>  $origins
     */
    public function origins(array $origins): self
    {
        foreach ($origins as $origin) {
            $this->addOrigin($origin);
        }

        return $this;
    }

    /**
     * Add a destination waypoint.
     *
     * @param  Waypoint|array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $destination
     */
    public function addDestination(Waypoint|array|string $destination): self
    {
        $this->destinations[] = $this->resolveWaypoint($destination);

        return $this;
    }

    /**
     * Set multiple destinations at once.
     *
     * @param  array<int, Waypoint|array|string>  $destinations
     */
    public function destinations(array $destinations): self
    {
        foreach ($destinations as $destination) {
            $this->addDestination($destination);
        }

        return $this;
    }

    /**
     * Set the travel mode.
     */
    public function travelMode(TravelMode $mode): self
    {
        $this->travelMode = $mode;

        return $this;
    }

    /**
     * Shortcut for driving mode.
     */
    public function driving(): self
    {
        return $this->travelMode(TravelMode::DRIVE);
    }

    /**
     * Shortcut for walking mode.
     */
    public function walking(): self
    {
        return $this->travelMode(TravelMode::WALK);
    }

    /**
     * Shortcut for bicycling mode.
     */
    public function bicycling(): self
    {
        return $this->travelMode(TravelMode::BICYCLE);
    }

    /**
     * Shortcut for transit mode.
     */
    public function transit(?TransitPreferences $preferences = null): self
    {
        $this->travelMode = TravelMode::TRANSIT;
        $this->transitPreferences = $preferences;

        return $this;
    }

    /**
     * Set the routing preference.
     */
    public function routingPreference(RoutingPreference $preference): self
    {
        $this->routingPreference = $preference;

        return $this;
    }

    /**
     * Enable traffic-aware routing.
     */
    public function withTraffic(): self
    {
        return $this->routingPreference(RoutingPreference::TRAFFIC_AWARE);
    }

    /**
     * Enable optimal traffic-aware routing.
     */
    public function withOptimalTraffic(): self
    {
        return $this->routingPreference(RoutingPreference::TRAFFIC_AWARE_OPTIMAL);
    }

    /**
     * Set the departure time.
     */
    public function departureTime(DateTimeInterface $time): self
    {
        $this->departureTime = $time;
        $this->arrivalTime = null;

        return $this;
    }

    /**
     * Shortcut for departure now.
     */
    public function departNow(): self
    {
        return $this->departureTime(new \DateTimeImmutable);
    }

    /**
     * Set the arrival time (for transit only).
     */
    public function arrivalTime(DateTimeInterface $time): self
    {
        $this->arrivalTime = $time;
        $this->departureTime = null;

        return $this;
    }

    /**
     * Set the language code for responses.
     */
    public function language(string $languageCode): self
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * Set the region code.
     */
    public function region(string $regionCode): self
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    /**
     * Set the unit system.
     */
    public function units(Units $units): self
    {
        $this->units = $units;

        return $this;
    }

    /**
     * Use metric units.
     */
    public function metric(): self
    {
        return $this->units(Units::METRIC);
    }

    /**
     * Use imperial units.
     */
    public function imperial(): self
    {
        return $this->units(Units::IMPERIAL);
    }

    /**
     * Add extra computations.
     */
    public function extraComputation(ExtraComputation $computation): self
    {
        if (! in_array($computation, $this->extraComputations)) {
            $this->extraComputations[] = $computation;
        }

        return $this;
    }

    /**
     * Include toll information.
     */
    public function withTolls(): self
    {
        return $this->extraComputation(ExtraComputation::TOLLS);
    }

    /**
     * Set the traffic model.
     */
    public function trafficModel(TrafficModel $model): self
    {
        $this->trafficModel = $model;

        return $this;
    }

    /**
     * Get the number of origins.
     */
    public function getOriginCount(): int
    {
        return count($this->origins);
    }

    /**
     * Get the number of destinations.
     */
    public function getDestinationCount(): int
    {
        return count($this->destinations);
    }

    /**
     * Execute the request and get the response.
     *
     * @throws InvalidRequestException
     */
    public function get(): RouteMatrixResponse
    {
        if ($this->client === null) {
            throw new InvalidRequestException(
                'No client available. Use GoogleRoutes facade or inject GoogleRoutesClientInterface.'
            );
        }

        return $this->client->computeRouteMatrix($this);
    }

    /**
     * Convert the request to an array for the API.
     *
     * @throws InvalidRequestException
     */
    public function toArray(): array
    {
        if (empty($this->origins)) {
            throw new InvalidRequestException('At least one origin is required');
        }

        if (empty($this->destinations)) {
            throw new InvalidRequestException('At least one destination is required');
        }

        // Validate size restrictions
        $this->validateSizeRestrictions();

        $data = [
            'origins' => array_map(function (array $origin) {
                $item = ['waypoint' => $origin['waypoint']->toArray()];
                if ($origin['routeModifiers'] !== null && $origin['routeModifiers']->hasModifiers()) {
                    $item['routeModifiers'] = $origin['routeModifiers']->toArray();
                }

                return $item;
            }, $this->origins),
            'destinations' => array_map(function (Waypoint $destination) {
                return ['waypoint' => $destination->toArray()];
            }, $this->destinations),
        ];

        if ($this->travelMode !== null) {
            $data['travelMode'] = $this->travelMode->value;
        }

        if ($this->routingPreference !== null) {
            $data['routingPreference'] = $this->routingPreference->value;
        }

        if ($this->departureTime !== null) {
            $data['departureTime'] = $this->departureTime->format(DateTimeInterface::RFC3339);
        }

        if ($this->arrivalTime !== null) {
            $data['arrivalTime'] = $this->arrivalTime->format(DateTimeInterface::RFC3339);
        }

        if ($this->languageCode !== null) {
            $data['languageCode'] = $this->languageCode;
        }

        if ($this->regionCode !== null) {
            $data['regionCode'] = $this->regionCode;
        }

        if ($this->units !== null) {
            $data['units'] = $this->units->value;
        }

        if (! empty($this->extraComputations)) {
            $data['extraComputations'] = array_map(
                fn (ExtraComputation $computation) => $computation->value,
                $this->extraComputations
            );
        }

        if ($this->trafficModel !== null) {
            $data['trafficModel'] = $this->trafficModel->value;
        }

        if ($this->transitPreferences !== null) {
            $preferences = $this->transitPreferences->toArray();
            if (! empty($preferences)) {
                $data['transitPreferences'] = $preferences;
            }
        }

        return $data;
    }

    /**
     * Validate API size restrictions.
     *
     * @throws InvalidRequestException
     */
    private function validateSizeRestrictions(): void
    {
        $originCount = count($this->origins);
        $destinationCount = count($this->destinations);
        $product = $originCount * $destinationCount;

        // The product must be no greater than 625
        if ($product > 625) {
            throw new InvalidRequestException(
                "The product of origins × destinations ({$product}) exceeds the limit of 625."
            );
        }

        // For TRAFFIC_AWARE_OPTIMAL, limit is 100
        if (
            $this->routingPreference === RoutingPreference::TRAFFIC_AWARE_OPTIMAL
            && $product > 100
        ) {
            throw new InvalidRequestException(
                "The product of origins × destinations ({$product}) exceeds the limit of 100 for TRAFFIC_AWARE_OPTIMAL."
            );
        }

        // For TRANSIT, limit is 100
        if ($this->travelMode === TravelMode::TRANSIT && $product > 100) {
            throw new InvalidRequestException(
                "The product of origins × destinations ({$product}) exceeds the limit of 100 for TRANSIT mode."
            );
        }
    }

    /**
     * Resolve a waypoint from various input types.
     */
    private function resolveWaypoint(Waypoint|array|string $input): Waypoint
    {
        if ($input instanceof Waypoint) {
            return $input;
        }

        if (is_array($input)) {
            return Waypoint::fromArray($input);
        }

        // Check for "lat,lng" string format
        if (preg_match('/^([-+]?\d{1,2}(?:\.\d+)?),\s*([-+]?\d{1,3}(?:\.\d+)?)$/', $input, $matches)) {
            return Waypoint::fromLatLng((float) $matches[1], (float) $matches[2]);
        }

        // String could be a place ID or address
        if (str_starts_with($input, 'ChIJ') || str_starts_with($input, 'place_id:')) {
            $placeId = str_replace('place_id:', '', $input);

            return Waypoint::fromPlaceId($placeId);
        }

        return Waypoint::fromAddress($input);
    }
}
