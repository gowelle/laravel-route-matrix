<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixElement;
use Gowelle\LaravelRouteMatrix\DataTransferObjects\RouteMatrixResponse;
use Gowelle\LaravelRouteMatrix\Enums\RouteMatrixElementCondition;

describe('RouteMatrixElement', function () {
    it('creates from array', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 1,
            'distanceMeters' => 5000,
            'duration' => '600s',
            'condition' => 'ROUTE_EXISTS',
        ]);

        expect($element->originIndex)->toBe(0)
            ->and($element->destinationIndex)->toBe(1)
            ->and($element->distanceMeters)->toBe(5000)
            ->and($element->duration)->toBe('600s')
            ->and($element->condition)->toBe(RouteMatrixElementCondition::ROUTE_EXISTS);
    });

    it('checks if route exists', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'distanceMeters' => 1000,
            'duration' => '120s',
            'condition' => 'ROUTE_EXISTS',
        ]);

        expect($element->routeExists())->toBeTrue();
    });

    it('checks if route not found', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'condition' => 'ROUTE_NOT_FOUND',
        ]);

        expect($element->routeExists())->toBeFalse();
    });

    it('gets duration in seconds', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'duration' => '3600s',
        ]);

        expect($element->getDurationInSeconds())->toBe(3600);
    });

    it('returns null duration when not set', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
        ]);

        expect($element->getDurationInSeconds())->toBeNull();
    });

    it('gets distance in kilometers', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'distanceMeters' => 5000,
        ]);

        expect($element->getDistanceInKilometers())->toBe(5.0);
    });

    it('gets distance in miles', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'distanceMeters' => 1609,
        ]);

        expect($element->getDistanceInMiles())->toBeGreaterThan(0.99)
            ->and($element->getDistanceInMiles())->toBeLessThan(1.01);
    });

    it('formats duration in seconds', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'duration' => '45s',
        ]);

        expect($element->getFormattedDuration())->toBe('45 sec');
    });

    it('formats duration in minutes', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'duration' => '300s',
        ]);

        expect($element->getFormattedDuration())->toBe('5 min');
    });

    it('formats duration in hours and minutes', function () {
        $element = RouteMatrixElement::fromArray([
            'originIndex' => 0,
            'destinationIndex' => 0,
            'duration' => '5400s', // 1h 30m
        ]);

        expect($element->getFormattedDuration())->toBe('1h 30m');
    });
});

describe('RouteMatrixResponse', function () {
    it('creates from array', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 2000],
        ], 1, 2);

        expect($response->count())->toBe(2)
            ->and($response->originCount)->toBe(1)
            ->and($response->destinationCount)->toBe(2);
    });

    it('gets element by indices', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 2000],
            ['originIndex' => 1, 'destinationIndex' => 0, 'distanceMeters' => 1500],
            ['originIndex' => 1, 'destinationIndex' => 1, 'distanceMeters' => 2500],
        ], 2, 2);

        $element = $response->get(1, 0);

        expect($element)->not->toBeNull()
            ->and($element->distanceMeters)->toBe(1500);
    });

    it('returns null for non-existent indices', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000],
        ], 1, 1);

        expect($response->get(5, 5))->toBeNull();
    });

    it('gets elements for specific origin', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 2000],
            ['originIndex' => 1, 'destinationIndex' => 0, 'distanceMeters' => 1500],
            ['originIndex' => 1, 'destinationIndex' => 1, 'distanceMeters' => 2500],
        ], 2, 2);

        $forOrigin0 = $response->getForOrigin(0);

        expect($forOrigin0)->toHaveCount(2)
            ->and($forOrigin0->first()->destinationIndex)->toBe(0);
    });

    it('gets elements for specific destination', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 2000],
            ['originIndex' => 1, 'destinationIndex' => 0, 'distanceMeters' => 1500],
            ['originIndex' => 1, 'destinationIndex' => 1, 'distanceMeters' => 2500],
        ], 2, 2);

        $forDest1 = $response->getForDestination(1);

        expect($forDest1)->toHaveCount(2);
    });

    it('gets closest destination', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 5000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 1000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 2, 'distanceMeters' => 3000, 'condition' => 'ROUTE_EXISTS'],
        ], 1, 3);

        $closest = $response->getClosestDestination(0);

        expect($closest)->not->toBeNull()
            ->and($closest->destinationIndex)->toBe(1)
            ->and($closest->distanceMeters)->toBe(1000);
    });

    it('gets fastest destination', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'duration' => '600s', 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 1, 'duration' => '120s', 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 2, 'duration' => '300s', 'condition' => 'ROUTE_EXISTS'],
        ], 1, 3);

        $fastest = $response->getFastestDestination(0);

        expect($fastest)->not->toBeNull()
            ->and($fastest->destinationIndex)->toBe(1)
            ->and($fastest->getDurationInSeconds())->toBe(120);
    });

    it('gets closest origin', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 5000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 1, 'destinationIndex' => 0, 'distanceMeters' => 1000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 2, 'destinationIndex' => 0, 'distanceMeters' => 3000, 'condition' => 'ROUTE_EXISTS'],
        ], 3, 1);

        $closest = $response->getClosestOrigin(0);

        expect($closest)->not->toBeNull()
            ->and($closest->originIndex)->toBe(1)
            ->and($closest->distanceMeters)->toBe(1000);
    });

    it('sorts by distance', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 5000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 1000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 2, 'distanceMeters' => 3000, 'condition' => 'ROUTE_EXISTS'],
        ], 1, 3);

        $sorted = $response->sortedByDistance();

        expect($sorted->first()->distanceMeters)->toBe(1000)
            ->and($sorted->last()->distanceMeters)->toBe(5000);
    });

    it('sorts by duration', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'duration' => '600s', 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 1, 'duration' => '120s', 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 2, 'duration' => '300s', 'condition' => 'ROUTE_EXISTS'],
        ], 1, 3);

        $sorted = $response->sortedByDuration();

        expect($sorted->first()->getDurationInSeconds())->toBe(120)
            ->and($sorted->last()->getDurationInSeconds())->toBe(600);
    });

    it('filters elements with routes', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 1, 'condition' => 'ROUTE_NOT_FOUND'],
            ['originIndex' => 0, 'destinationIndex' => 2, 'distanceMeters' => 3000, 'condition' => 'ROUTE_EXISTS'],
        ], 1, 3);

        $withRoutes = $response->withRoutes();

        expect($withRoutes)->toHaveCount(2);
    });

    it('filters elements without routes', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000, 'condition' => 'ROUTE_EXISTS'],
            ['originIndex' => 0, 'destinationIndex' => 1, 'condition' => 'ROUTE_NOT_FOUND'],
            ['originIndex' => 0, 'destinationIndex' => 2, 'distanceMeters' => 3000, 'condition' => 'ROUTE_EXISTS'],
        ], 1, 3);

        $withoutRoutes = $response->withoutRoutes();

        expect($withoutRoutes)->toHaveCount(1)
            ->and($withoutRoutes->first()->destinationIndex)->toBe(1);
    });

    it('checks if has routes', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000, 'condition' => 'ROUTE_EXISTS'],
        ], 1, 1);

        expect($response->hasRoutes())->toBeTrue();
    });

    it('checks if no routes', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'condition' => 'ROUTE_NOT_FOUND'],
        ], 1, 1);

        expect($response->hasRoutes())->toBeFalse();
    });

    it('converts to 2D matrix', function () {
        $response = RouteMatrixResponse::fromArray([
            ['originIndex' => 0, 'destinationIndex' => 0, 'distanceMeters' => 1000],
            ['originIndex' => 0, 'destinationIndex' => 1, 'distanceMeters' => 2000],
            ['originIndex' => 1, 'destinationIndex' => 0, 'distanceMeters' => 1500],
            ['originIndex' => 1, 'destinationIndex' => 1, 'distanceMeters' => 2500],
        ], 2, 2);

        $matrix = $response->toMatrix();

        expect($matrix)->toHaveCount(2)
            ->and($matrix[0])->toHaveCount(2)
            ->and($matrix[1])->toHaveCount(2)
            ->and($matrix[0][0]->distanceMeters)->toBe(1000)
            ->and($matrix[0][1]->distanceMeters)->toBe(2000)
            ->and($matrix[1][0]->distanceMeters)->toBe(1500)
            ->and($matrix[1][1]->distanceMeters)->toBe(2500);
    });
});
