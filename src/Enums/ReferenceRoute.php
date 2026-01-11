<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * A supported reference route on the ComputeRoutesRequest.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/TopLevel/computeRoutes#ReferenceRoute
 */
enum ReferenceRoute: string
{
    /**
     * Not specified. Default value. Won't compute a reference route.
     */
    case REFERENCE_ROUTE_UNSPECIFIED = 'REFERENCE_ROUTE_UNSPECIFIED';

    /**
     * Fuel efficient route. Routes labeled with this value are
     * optimized for fuel consumption.
     */
    case FUEL_EFFICIENT = 'FUEL_EFFICIENT';

    /**
     * Route with shorter travel distance. This is an experimental feature.
     * For DRIVE requests, this feature prioritizes shorter distance over
     * driving comfort.
     */
    case SHORTER_DISTANCE = 'SHORTER_DISTANCE';
}
