<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Extra computations to perform while completing the request.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/TopLevel/computeRoutes#ExtraComputation
 */
enum ExtraComputation: string
{
    /**
     * Not specified. Default value. Won't compute extra computations.
     */
    case EXTRA_COMPUTATION_UNSPECIFIED = 'EXTRA_COMPUTATION_UNSPECIFIED';

    /**
     * Toll information for the route(s).
     */
    case TOLLS = 'TOLLS';

    /**
     * Estimated fuel consumption for the route(s).
     */
    case FUEL_CONSUMPTION = 'FUEL_CONSUMPTION';

    /**
     * Traffic aware polyline for the route(s).
     */
    case TRAFFIC_ON_POLYLINE = 'TRAFFIC_ON_POLYLINE';

    /**
     * NavigationInstructions presented as a formatted HTML text string.
     */
    case HTML_FORMATTED_NAVIGATION_INSTRUCTIONS = 'HTML_FORMATTED_NAVIGATION_INSTRUCTIONS';

    /**
     * Flyover information for the route(s).
     */
    case FLYOVER_INFO_ON_POLYLINE = 'FLYOVER_INFO_ON_POLYLINE';

    /**
     * Narrow road information for the route(s).
     */
    case NARROW_ROAD_INFO_ON_POLYLINE = 'NARROW_ROAD_INFO_ON_POLYLINE';
}
