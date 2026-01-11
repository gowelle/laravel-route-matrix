<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Specifies how to compute the route.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/RoutingPreference
 */
enum RoutingPreference: string
{
    /**
     * No routing preference specified. Defaults to TRAFFIC_UNAWARE.
     */
    case ROUTING_PREFERENCE_UNSPECIFIED = 'ROUTING_PREFERENCE_UNSPECIFIED';

    /**
     * Computes routes without taking live traffic conditions into consideration.
     * Suitable when traffic conditions don't matter or are not applicable.
     */
    case TRAFFIC_UNAWARE = 'TRAFFIC_UNAWARE';

    /**
     * Computes routes taking live traffic conditions into consideration.
     * This may include slight routing changes to avoid congestion.
     */
    case TRAFFIC_AWARE = 'TRAFFIC_AWARE';

    /**
     * Computes the best route taking live traffic conditions into consideration.
     * Considers more routing options to avoid congestion.
     * Slower than TRAFFIC_AWARE but produces better results.
     */
    case TRAFFIC_AWARE_OPTIMAL = 'TRAFFIC_AWARE_OPTIMAL';
}
