<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Specifies the quality of the polyline.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/TopLevel/computeRoutes#PolylineQuality
 */
enum PolylineQuality: string
{
    /**
     * No polyline quality specified. Defaults to OVERVIEW.
     */
    case POLYLINE_QUALITY_UNSPECIFIED = 'POLYLINE_QUALITY_UNSPECIFIED';

    /**
     * Specifies a high-quality polyline with more points for a more
     * accurate representation of the route. Use this value when you
     * need precise route geometry.
     */
    case HIGH_QUALITY = 'HIGH_QUALITY';

    /**
     * Specifies an overview polyline with fewer points.
     * Use this value when displaying an overview of the route.
     */
    case OVERVIEW = 'OVERVIEW';
}
