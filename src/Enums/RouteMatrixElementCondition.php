<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Enums;

/**
 * Condition of the route element in the matrix.
 */
enum RouteMatrixElementCondition: string
{
    /**
     * Unspecified condition.
     */
    case ROUTE_MATRIX_ELEMENT_CONDITION_UNSPECIFIED = 'ROUTE_MATRIX_ELEMENT_CONDITION_UNSPECIFIED';

    /**
     * A route was found, and the corresponding information was filled out for the element.
     */
    case ROUTE_EXISTS = 'ROUTE_EXISTS';

    /**
     * No route could be found.
     */
    case ROUTE_NOT_FOUND = 'ROUTE_NOT_FOUND';
}
