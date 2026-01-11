<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix;

use DateTimeInterface;
use Gowelle\LaravelRouteMatrix\Contracts\GoogleRoutesClientInterface;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\Enums\ExtraComputation;
use Gowelle\LaravelRouteMatrix\Enums\PolylineEncoding;
use Gowelle\LaravelRouteMatrix\Enums\PolylineQuality;
use Gowelle\LaravelRouteMatrix\Enums\ReferenceRoute;
use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Gowelle\LaravelRouteMatrix\Enums\TrafficModel;
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;
use Gowelle\LaravelRouteMatrix\Enums\Units;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\ValueObjects\RouteModifiers;
use Gowelle\LaravelRouteMatrix\ValueObjects\TransitPreferences;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

/**
 * Fluent builder for constructing route requests.
 */
class RouteRequest
{
    private ?Waypoint $origin = null;

    private ?Waypoint $destination = null;

    /** @var array<int, Waypoint> */
    private array $intermediates = [];

    private ?TravelMode $travelMode = null;

    private ?RoutingPreference $routingPreference = null;

    private ?PolylineQuality $polylineQuality = null;

    private ?PolylineEncoding $polylineEncoding = null;

    private ?DateTimeInterface $departureTime = null;

    private ?DateTimeInterface $arrivalTime = null;

    private bool $computeAlternativeRoutes = false;

    private ?RouteModifiers $routeModifiers = null;

    private ?string $languageCode = null;

    private ?string $regionCode = null;

    private ?Units $units = null;

    private bool $optimizeWaypointOrder = false;

    /** @var array<int, ReferenceRoute> */
    private array $requestedReferenceRoutes = [];

    /** @var array<int, ExtraComputation> */
    private array $extraComputations = [];

    private ?TrafficModel $trafficModel = null;

    private ?TransitPreferences $transitPreferences = null;

    /** @var array<int, string> */
    private array $fieldMask = [];

    private ?GoogleRoutesClientInterface $client = null;

    /**
     * Create a new RouteRequest with optional client.
     */
    public function __construct(?GoogleRoutesClientInterface $client = null)
    {
        $this->client = $client;
    }

    /**
     * Set the origin waypoint.
     *
     * @param  Waypoint|array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $origin
     */
    public function from(Waypoint|array|string $origin): self
    {
        $this->origin = $this->resolveWaypoint($origin);

        return $this;
    }

    /**
     * Set the destination waypoint.
     *
     * @param  Waypoint|array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $destination
     */
    public function to(Waypoint|array|string $destination): self
    {
        $this->destination = $this->resolveWaypoint($destination);

        return $this;
    }

    /**
     * Add an intermediate waypoint.
     *
     * @param  Waypoint|array{lat?: float, latitude?: float, lng?: float, longitude?: float}|string  $waypoint
     */
    public function via(Waypoint|array|string $waypoint, bool $stopover = true): self
    {
        $resolved = $this->resolveWaypoint($waypoint);

        // If not a stopover, create a new waypoint with via = true
        if (! $stopover && $resolved->location !== null) {
            $resolved = Waypoint::fromLatLng(
                $resolved->location->latLng->latitude,
                $resolved->location->latLng->longitude,
                via: true,
            );
        }

        $this->intermediates[] = $resolved;

        return $this;
    }

    /**
     * Add multiple intermediate waypoints.
     *
     * @param  array<int, Waypoint|array|string>  $waypoints
     */
    public function waypoints(array $waypoints): self
    {
        foreach ($waypoints as $waypoint) {
            $this->via($waypoint);
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
     * Set the polyline quality.
     */
    public function polylineQuality(PolylineQuality $quality): self
    {
        $this->polylineQuality = $quality;

        return $this;
    }

    /**
     * Set the polyline encoding.
     */
    public function polylineEncoding(PolylineEncoding $encoding): self
    {
        $this->polylineEncoding = $encoding;

        return $this;
    }

    /**
     * Request high quality polyline.
     */
    public function highQualityPolyline(): self
    {
        return $this->polylineQuality(PolylineQuality::HIGH_QUALITY);
    }

    /**
     * Request GeoJSON polyline format.
     */
    public function geoJsonPolyline(): self
    {
        return $this->polylineEncoding(PolylineEncoding::GEO_JSON_LINESTRING);
    }

    /**
     * Set the departure time.
     */
    public function departureTime(DateTimeInterface $time): self
    {
        $this->departureTime = $time;
        $this->arrivalTime = null; // Can't have both

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
        $this->departureTime = null; // Can't have both

        return $this;
    }

    /**
     * Enable computation of alternative routes.
     */
    public function computeAlternativeRoutes(bool $compute = true): self
    {
        $this->computeAlternativeRoutes = $compute;

        return $this;
    }

    /**
     * Alias for computeAlternativeRoutes.
     */
    public function withAlternatives(): self
    {
        return $this->computeAlternativeRoutes(true);
    }

    /**
     * Set route modifiers.
     */
    public function routeModifiers(RouteModifiers $modifiers): self
    {
        $this->routeModifiers = $modifiers;

        return $this;
    }

    /**
     * Avoid tolls.
     */
    public function avoidTolls(bool $avoid = true): self
    {
        $this->routeModifiers = new RouteModifiers(
            avoidTolls: $avoid,
            avoidHighways: $this->routeModifiers?->avoidHighways ?? false,
            avoidFerries: $this->routeModifiers?->avoidFerries ?? false,
            avoidIndoor: $this->routeModifiers?->avoidIndoor ?? false,
        );

        return $this;
    }

    /**
     * Avoid highways.
     */
    public function avoidHighways(bool $avoid = true): self
    {
        $this->routeModifiers = new RouteModifiers(
            avoidTolls: $this->routeModifiers?->avoidTolls ?? false,
            avoidHighways: $avoid,
            avoidFerries: $this->routeModifiers?->avoidFerries ?? false,
            avoidIndoor: $this->routeModifiers?->avoidIndoor ?? false,
        );

        return $this;
    }

    /**
     * Avoid ferries.
     */
    public function avoidFerries(bool $avoid = true): self
    {
        $this->routeModifiers = new RouteModifiers(
            avoidTolls: $this->routeModifiers?->avoidTolls ?? false,
            avoidHighways: $this->routeModifiers?->avoidHighways ?? false,
            avoidFerries: $avoid,
            avoidIndoor: $this->routeModifiers?->avoidIndoor ?? false,
        );

        return $this;
    }

    /**
     * Avoid indoor navigation.
     */
    public function avoidIndoor(bool $avoid = true): self
    {
        $this->routeModifiers = new RouteModifiers(
            avoidTolls: $this->routeModifiers?->avoidTolls ?? false,
            avoidHighways: $this->routeModifiers?->avoidHighways ?? false,
            avoidFerries: $this->routeModifiers?->avoidFerries ?? false,
            avoidIndoor: $avoid,
        );

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
     * Enable waypoint order optimization.
     */
    public function optimizeWaypointOrder(bool $optimize = true): self
    {
        $this->optimizeWaypointOrder = $optimize;

        return $this;
    }

    /**
     * Request a reference route.
     */
    public function requestReferenceRoute(ReferenceRoute $route): self
    {
        if (! in_array($route, $this->requestedReferenceRoutes)) {
            $this->requestedReferenceRoutes[] = $route;
        }

        return $this;
    }

    /**
     * Request fuel-efficient route.
     */
    public function withFuelEfficientRoute(): self
    {
        return $this->requestReferenceRoute(ReferenceRoute::FUEL_EFFICIENT);
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
     * Include fuel consumption estimates.
     */
    public function withFuelConsumption(): self
    {
        return $this->extraComputation(ExtraComputation::FUEL_CONSUMPTION);
    }

    /**
     * Include traffic on polyline.
     */
    public function withTrafficOnPolyline(): self
    {
        return $this->extraComputation(ExtraComputation::TRAFFIC_ON_POLYLINE);
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
     * Set the response field mask.
     *
     * @param  array<int, string>  $fields
     */
    public function fields(array $fields): self
    {
        $this->fieldMask = $fields;

        return $this;
    }

    /**
     * Get the current field mask.
     *
     * @return array<int, string>
     */
    public function getFieldMask(): array
    {
        if (! empty($this->fieldMask)) {
            return $this->fieldMask;
        }

        // Return default field mask from config
        return config('google-routes.default_field_mask', [
            'routes.duration',
            'routes.distanceMeters',
            'routes.polyline.encodedPolyline',
        ]);
    }

    /**
     * Execute the request and get the response.
     *
     * @throws InvalidRequestException
     */
    public function get(): RoutesResponse
    {
        if ($this->client === null) {
            throw new InvalidRequestException(
                'No client available. Use GoogleRoutes facade or inject GoogleRoutesClientInterface.'
            );
        }

        return $this->client->computeRoutes($this);
    }

    /**
     * Convert the request to an array for the API.
     *
     * @throws InvalidRequestException
     */
    public function toArray(): array
    {
        if ($this->origin === null) {
            throw new InvalidRequestException('Origin waypoint is required');
        }

        if ($this->destination === null) {
            throw new InvalidRequestException('Destination waypoint is required');
        }

        $data = [
            'origin' => $this->origin->toArray(),
            'destination' => $this->destination->toArray(),
        ];

        if (! empty($this->intermediates)) {
            $data['intermediates'] = array_map(
                fn (Waypoint $waypoint) => $waypoint->toArray(),
                $this->intermediates
            );
        }

        if ($this->travelMode !== null) {
            $data['travelMode'] = $this->travelMode->value;
        }

        if ($this->routingPreference !== null) {
            $data['routingPreference'] = $this->routingPreference->value;
        }

        if ($this->polylineQuality !== null) {
            $data['polylineQuality'] = $this->polylineQuality->value;
        }

        if ($this->polylineEncoding !== null) {
            $data['polylineEncoding'] = $this->polylineEncoding->value;
        }

        if ($this->departureTime !== null) {
            $data['departureTime'] = $this->departureTime->format(\DateTimeInterface::RFC3339);
        }

        if ($this->arrivalTime !== null) {
            $data['arrivalTime'] = $this->arrivalTime->format(\DateTimeInterface::RFC3339);
        }

        if ($this->computeAlternativeRoutes) {
            $data['computeAlternativeRoutes'] = true;
        }

        if ($this->routeModifiers !== null && $this->routeModifiers->hasModifiers()) {
            $data['routeModifiers'] = $this->routeModifiers->toArray();
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

        if ($this->optimizeWaypointOrder) {
            $data['optimizeWaypointOrder'] = true;
        }

        if (! empty($this->requestedReferenceRoutes)) {
            $data['requestedReferenceRoutes'] = array_map(
                fn (ReferenceRoute $route) => $route->value,
                $this->requestedReferenceRoutes
            );
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
