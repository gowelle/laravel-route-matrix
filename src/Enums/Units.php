<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Specifies the units of measure for the display fields.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/Units
 */
enum Units: string
{
    /**
     * Units of measure not specified. Defaults to the unit of measure
     * inferred from the request.
     */
    case UNITS_UNSPECIFIED = 'UNITS_UNSPECIFIED';

    /**
     * Metric units of measure (kilometers, meters).
     */
    case METRIC = 'METRIC';

    /**
     * Imperial (English) units of measure (miles, feet).
     */
    case IMPERIAL = 'IMPERIAL';
}
