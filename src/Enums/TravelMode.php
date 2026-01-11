<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Specifies the mode of transportation.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/RouteTravelMode
 */
enum TravelMode: string
{
    /**
     * No travel mode specified. Defaults to DRIVE.
     */
    case TRAVEL_MODE_UNSPECIFIED = 'TRAVEL_MODE_UNSPECIFIED';

    /**
     * Travel by passenger car.
     */
    case DRIVE = 'DRIVE';

    /**
     * Travel by bicycle.
     */
    case BICYCLE = 'BICYCLE';

    /**
     * Travel by walking.
     */
    case WALK = 'WALK';

    /**
     * Two-wheeled, motorized vehicle (e.g., motorcycle).
     * Note: This is not available in all regions.
     */
    case TWO_WHEELER = 'TWO_WHEELER';

    /**
     * Travel by public transit.
     */
    case TRANSIT = 'TRANSIT';
}
