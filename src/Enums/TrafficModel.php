<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Specifies the assumptions to use when calculating time in traffic.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/TrafficModel
 */
enum TrafficModel: string
{
    /**
     * Unused. Traffic model is not specified.
     */
    case TRAFFIC_MODEL_UNSPECIFIED = 'TRAFFIC_MODEL_UNSPECIFIED';

    /**
     * Indicates that the returned duration should be the best estimate
     * of travel time given what is known about both historical traffic
     * conditions and live traffic.
     */
    case BEST_GUESS = 'BEST_GUESS';

    /**
     * Indicates that the returned duration should be longer than the
     * actual travel time on most days, though occasional days with
     * particularly bad traffic conditions may exceed this value.
     */
    case PESSIMISTIC = 'PESSIMISTIC';

    /**
     * Indicates that the returned duration should be shorter than the
     * actual travel time on most days, though occasional days with
     * particularly good traffic conditions may be faster than this value.
     */
    case OPTIMISTIC = 'OPTIMISTIC';
}
