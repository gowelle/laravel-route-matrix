<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Contracts;

use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

interface Routable
{
    /**
     * Get the waypoint representation of the object.
     */
    public function getWaypoint(): Waypoint;
}
