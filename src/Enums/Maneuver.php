<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * A set of values that specify the navigation action for a step.
 *
 * @see https://developers.google.com/maps/documentation/routes/reference/rest/v2/TopLevel/computeRoutes#Maneuver
 */
enum Maneuver: string
{
    /**
     * Not specified.
     */
    case MANEUVER_UNSPECIFIED = 'MANEUVER_UNSPECIFIED';

    /**
     * Turn slightly to the left.
     */
    case TURN_SLIGHT_LEFT = 'TURN_SLIGHT_LEFT';

    /**
     * Turn sharply to the left.
     */
    case TURN_SHARP_LEFT = 'TURN_SHARP_LEFT';

    /**
     * Make a left u-turn.
     */
    case UTURN_LEFT = 'UTURN_LEFT';

    /**
     * Turn left.
     */
    case TURN_LEFT = 'TURN_LEFT';

    /**
     * Turn slightly to the right.
     */
    case TURN_SLIGHT_RIGHT = 'TURN_SLIGHT_RIGHT';

    /**
     * Turn sharply to the right.
     */
    case TURN_SHARP_RIGHT = 'TURN_SHARP_RIGHT';

    /**
     * Make a right u-turn.
     */
    case UTURN_RIGHT = 'UTURN_RIGHT';

    /**
     * Turn right.
     */
    case TURN_RIGHT = 'TURN_RIGHT';

    /**
     * Go straight.
     */
    case STRAIGHT = 'STRAIGHT';

    /**
     * Take the left ramp.
     */
    case RAMP_LEFT = 'RAMP_LEFT';

    /**
     * Take the right ramp.
     */
    case RAMP_RIGHT = 'RAMP_RIGHT';

    /**
     * Merge into traffic.
     */
    case MERGE = 'MERGE';

    /**
     * Take the left fork.
     */
    case FORK_LEFT = 'FORK_LEFT';

    /**
     * Take the right fork.
     */
    case FORK_RIGHT = 'FORK_RIGHT';

    /**
     * Take the ferry.
     */
    case FERRY = 'FERRY';

    /**
     * Take the train leading onto the ferry.
     */
    case FERRY_TRAIN = 'FERRY_TRAIN';

    /**
     * Turn left at the roundabout.
     */
    case ROUNDABOUT_LEFT = 'ROUNDABOUT_LEFT';

    /**
     * Turn right at the roundabout.
     */
    case ROUNDABOUT_RIGHT = 'ROUNDABOUT_RIGHT';

    /**
     * Initial maneuver.
     */
    case DEPART = 'DEPART';

    /**
     * Used to indicate a street name change.
     */
    case NAME_CHANGE = 'NAME_CHANGE';
}
