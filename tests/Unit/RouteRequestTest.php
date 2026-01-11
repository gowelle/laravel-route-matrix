<?php

declare(strict_types=1);

use Gowelle\LaravelRouteMatrix\Enums\PolylineEncoding;
use Gowelle\LaravelRouteMatrix\Enums\PolylineQuality;
use Gowelle\LaravelRouteMatrix\Enums\RoutingPreference;
use Gowelle\LaravelRouteMatrix\Enums\TravelMode;
use Gowelle\LaravelRouteMatrix\Enums\Units;
use Gowelle\LaravelRouteMatrix\Exceptions\InvalidRequestException;
use Gowelle\LaravelRouteMatrix\RouteRequest;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;

describe('RouteRequest', function () {
    it('builds a basic request', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data)->toHaveKey('origin')
            ->and($data)->toHaveKey('destination')
            ->and($data['origin']['location']['latLng']['latitude'])->toBe(37.419734);
    });

    it('throws when origin is missing', function () {
        $request = new RouteRequest;
        $request->to(['lat' => 37.417670, 'lng' => -122.079595]);
        $request->toArray();
    })->throws(InvalidRequestException::class, 'Origin waypoint is required');

    it('throws when destination is missing', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784]);
        $request->toArray();
    })->throws(InvalidRequestException::class, 'Destination waypoint is required');

    it('accepts Waypoint objects', function () {
        $request = new RouteRequest;
        $request->from(Waypoint::fromLatLng(37.419734, -122.0827784))
            ->to(Waypoint::fromPlaceId('ChIJ2eUgeAK6j4ARbn5u_wAGqWA'));

        $data = $request->toArray();

        expect($data['origin'])->toHaveKey('location')
            ->and($data['destination'])->toHaveKey('placeId');
    });

    it('accepts string addresses', function () {
        $request = new RouteRequest;
        $request->from('1600 Amphitheatre Parkway, Mountain View, CA')
            ->to('1 Infinite Loop, Cupertino, CA');

        $data = $request->toArray();

        expect($data['origin'])->toHaveKey('address')
            ->and($data['destination'])->toHaveKey('address');
    });

    it('adds intermediate waypoints', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->via(['lat' => 37.418, 'lng' => -122.081])
            ->via(['lat' => 37.416, 'lng' => -122.080])
            ->to(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data)->toHaveKey('intermediates')
            ->and($data['intermediates'])->toHaveCount(2);
    });

    it('sets travel mode', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->travelMode(TravelMode::DRIVE);

        $data = $request->toArray();

        expect($data['travelMode'])->toBe('DRIVE');
    });

    it('has driving shortcut', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->driving();

        $data = $request->toArray();

        expect($data['travelMode'])->toBe('DRIVE');
    });

    it('has walking shortcut', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->walking();

        $data = $request->toArray();

        expect($data['travelMode'])->toBe('WALK');
    });

    it('sets routing preference', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->routingPreference(RoutingPreference::TRAFFIC_AWARE);

        $data = $request->toArray();

        expect($data['routingPreference'])->toBe('TRAFFIC_AWARE');
    });

    it('has withTraffic shortcut', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->withTraffic();

        $data = $request->toArray();

        expect($data['routingPreference'])->toBe('TRAFFIC_AWARE');
    });

    it('sets polyline quality', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->polylineQuality(PolylineQuality::HIGH_QUALITY);

        $data = $request->toArray();

        expect($data['polylineQuality'])->toBe('HIGH_QUALITY');
    });

    it('sets polyline encoding', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->polylineEncoding(PolylineEncoding::GEO_JSON_LINESTRING);

        $data = $request->toArray();

        expect($data['polylineEncoding'])->toBe('GEO_JSON_LINESTRING');
    });

    it('sets departure time', function () {
        $time = new DateTimeImmutable('2026-01-15T10:00:00+00:00');

        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->departureTime($time);

        $data = $request->toArray();

        expect($data)->toHaveKey('departureTime')
            ->and($data['departureTime'])->toContain('2026-01-15');
    });

    it('enables alternative routes', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->computeAlternativeRoutes();

        $data = $request->toArray();

        expect($data['computeAlternativeRoutes'])->toBeTrue();
    });

    it('sets route modifiers', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->avoidTolls()
            ->avoidHighways();

        $data = $request->toArray();

        expect($data['routeModifiers']['avoidTolls'])->toBeTrue()
            ->and($data['routeModifiers']['avoidHighways'])->toBeTrue();
    });

    it('sets language code', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->language('es-ES');

        $data = $request->toArray();

        expect($data['languageCode'])->toBe('es-ES');
    });

    it('sets units', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->units(Units::IMPERIAL);

        $data = $request->toArray();

        expect($data['units'])->toBe('IMPERIAL');
    });

    it('has metric shortcut', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->metric();

        $data = $request->toArray();

        expect($data['units'])->toBe('METRIC');
    });

    it('enables waypoint optimization', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->via(['lat' => 37.418, 'lng' => -122.081])
            ->via(['lat' => 37.416, 'lng' => -122.080])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->optimizeWaypointOrder();

        $data = $request->toArray();

        expect($data['optimizeWaypointOrder'])->toBeTrue();
    });

    it('requests fuel-efficient route', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->withFuelEfficientRoute();

        $data = $request->toArray();

        expect($data['requestedReferenceRoutes'])->toContain('FUEL_EFFICIENT');
    });

    it('resolves place IDs from strings', function () {
        $request = new RouteRequest;
        $request->from('ChIJ2eUgeAK6j4ARbn5u_wAGqWA')
            ->to(['lat' => 37.417670, 'lng' => -122.079595]);

        $data = $request->toArray();

        expect($data['origin'])->toHaveKey('placeId');
    });

    it('sets custom field mask', function () {
        $request = new RouteRequest;
        $request->from(['lat' => 37.419734, 'lng' => -122.0827784])
            ->to(['lat' => 37.417670, 'lng' => -122.079595])
            ->fields(['routes.duration', 'routes.distanceMeters']);

        expect($request->getFieldMask())->toBe([
            'routes.duration',
            'routes.distanceMeters',
        ]);
    });
});
