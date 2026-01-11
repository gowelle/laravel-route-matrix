<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\ValueObjects\LatLng;
use Gowelle\LaravelRouteMatrix\ValueObjects\RouteModifiers;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

describe('LatLng', function () {
    it('creates from coordinates', function () {
        $latLng = new LatLng(37.419734, -122.0827784);

        expect($latLng->latitude)->toBe(37.419734)
            ->and($latLng->longitude)->toBe(-122.0827784);
    });

    it('creates from array', function () {
        $latLng = LatLng::fromArray(['lat' => 37.419734, 'lng' => -122.0827784]);

        expect($latLng->latitude)->toBe(37.419734)
            ->and($latLng->longitude)->toBe(-122.0827784);
    });

    it('creates from array with full keys', function () {
        $latLng = LatLng::fromArray(['latitude' => 37.419734, 'longitude' => -122.0827784]);

        expect($latLng->latitude)->toBe(37.419734)
            ->and($latLng->longitude)->toBe(-122.0827784);
    });

    it('creates from string', function () {
        $latLng = LatLng::fromString('37.419734,-122.0827784');

        expect($latLng->latitude)->toBe(37.419734)
            ->and($latLng->longitude)->toBe(-122.0827784);
    });

    it('validates latitude range', function () {
        new LatLng(91, 0);
    })->throws(InvalidArgumentException::class);

    it('validates longitude range', function () {
        new LatLng(0, 181);
    })->throws(InvalidArgumentException::class);

    it('converts to array', function () {
        $latLng = new LatLng(37.419734, -122.0827784);

        expect($latLng->toArray())->toBe([
            'latitude' => 37.419734,
            'longitude' => -122.0827784,
        ]);
    });

    it('converts to string', function () {
        $latLng = new LatLng(37.419734, -122.0827784);

        expect((string) $latLng)->toBe('37.419734,-122.0827784');
    });
});

describe('Waypoint', function () {
    it('creates from lat/lng', function () {
        $waypoint = Waypoint::fromLatLng(37.419734, -122.0827784);

        expect($waypoint->location)->not->toBeNull()
            ->and($waypoint->placeId)->toBeNull()
            ->and($waypoint->address)->toBeNull();
    });

    it('creates from array', function () {
        $waypoint = Waypoint::fromArray(['lat' => 37.419734, 'lng' => -122.0827784]);

        expect($waypoint->location)->not->toBeNull();
    });

    it('creates from place ID', function () {
        $waypoint = Waypoint::fromPlaceId('ChIJ2eUgeAK6j4ARbn5u_wAGqWA');

        expect($waypoint->placeId)->toBe('ChIJ2eUgeAK6j4ARbn5u_wAGqWA')
            ->and($waypoint->location)->toBeNull()
            ->and($waypoint->address)->toBeNull();
    });

    it('creates from address', function () {
        $waypoint = Waypoint::fromAddress('1600 Amphitheatre Parkway, Mountain View, CA');

        expect($waypoint->address)->toBe('1600 Amphitheatre Parkway, Mountain View, CA')
            ->and($waypoint->location)->toBeNull()
            ->and($waypoint->placeId)->toBeNull();
    });

    it('supports via waypoints', function () {
        $waypoint = Waypoint::fromLatLng(37.419734, -122.0827784, via: true);

        expect($waypoint->via)->toBeTrue();
    });

    it('converts location waypoint to array', function () {
        $waypoint = Waypoint::fromLatLng(37.419734, -122.0827784);

        expect($waypoint->toArray())->toHaveKey('location')
            ->and($waypoint->toArray()['location'])->toHaveKey('latLng');
    });

    it('converts place ID waypoint to array', function () {
        $waypoint = Waypoint::fromPlaceId('ChIJ2eUgeAK6j4ARbn5u_wAGqWA');

        expect($waypoint->toArray())->toBe([
            'placeId' => 'ChIJ2eUgeAK6j4ARbn5u_wAGqWA',
        ]);
    });

    it('converts address waypoint to array', function () {
        $waypoint = Waypoint::fromAddress('1600 Amphitheatre Parkway');

        expect($waypoint->toArray())->toBe([
            'address' => '1600 Amphitheatre Parkway',
        ]);
    });
});

describe('RouteModifiers', function () {
    it('creates with defaults', function () {
        $modifiers = new RouteModifiers;

        expect($modifiers->avoidTolls)->toBeFalse()
            ->and($modifiers->avoidHighways)->toBeFalse()
            ->and($modifiers->avoidFerries)->toBeFalse()
            ->and($modifiers->avoidIndoor)->toBeFalse()
            ->and($modifiers->hasModifiers())->toBeFalse();
    });

    it('detects when modifiers are set', function () {
        $modifiers = new RouteModifiers(avoidTolls: true);

        expect($modifiers->hasModifiers())->toBeTrue();
    });

    it('creates from array', function () {
        $modifiers = RouteModifiers::fromArray([
            'avoidTolls' => true,
            'avoidHighways' => true,
        ]);

        expect($modifiers->avoidTolls)->toBeTrue()
            ->and($modifiers->avoidHighways)->toBeTrue()
            ->and($modifiers->avoidFerries)->toBeFalse();
    });
});
