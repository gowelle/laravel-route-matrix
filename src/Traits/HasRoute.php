<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Traits;

use Gowelle\LaravelRouteMatrix\Contracts\Routable;
use Gowelle\LaravelRouteMatrix\Facades\GoogleRoutes;
use Gowelle\LaravelRouteMatrix\RouteRequest;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

trait HasRoute
{
    /**
     * Get the waypoint representation of the object.
     *
     * By default, it looks for common location attributes.
     * Override this method for custom logic.
     */
    public function getWaypoint(): Waypoint
    {
        // Check for Lat/Lng attributes
        $lat = $this->getAttribute('lat') ?? $this->getAttribute('latitude');
        $lng = $this->getAttribute('lng') ?? $this->getAttribute('longitude');

        if ($lat && $lng) {
            return Waypoint::fromLatLng((float) $lat, (float) $lng);
        }

        // Check for Place ID
        if ($placeId = $this->getAttribute('place_id')) {
            return Waypoint::fromPlaceId($placeId);
        }

        // Check for Address
        if ($address = $this->getAttribute('address')) {
            return Waypoint::fromAddress($address);
        }

        throw new \RuntimeException('Could not determine waypoint from model attributes. Please override getWaypoint().');
    }

    /**
     * Start a route request from this object.
     */
    public function routeTo(Routable|string|array $destination): RouteRequest
    {
        return GoogleRoutes::from($this->getWaypoint())->to($destination);
    }
}
