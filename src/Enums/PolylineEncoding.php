<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Specifies the preferred encoding for the polyline.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/TopLevel/computeRoutes#PolylineEncoding
 */
enum PolylineEncoding: string
{
    /**
     * No polyline encoding specified. Defaults to ENCODED_POLYLINE.
     */
    case POLYLINE_ENCODING_UNSPECIFIED = 'POLYLINE_ENCODING_UNSPECIFIED';

    /**
     * Specifies a polyline encoded using the polyline encoding algorithm.
     *
     * @see https://developers.google.com/maps/documentation/utilities/polylinealgorithm
     */
    case ENCODED_POLYLINE = 'ENCODED_POLYLINE';

    /**
     * Specifies a polyline using the GeoJSON LineString format.
     *
     * @see https://tools.ietf.org/html/rfc7946#section-3.1.4
     */
    case GEO_JSON_LINESTRING = 'GEO_JSON_LINESTRING';
}
