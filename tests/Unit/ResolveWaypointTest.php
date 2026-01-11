<?php

declare(strict_types=1);

namespace Gowelle\LaravelRouteMatrix\Tests\Unit;

use Gowelle\LaravelRouteMatrix\RouteMatrixRequest;
use Gowelle\LaravelRouteMatrix\RouteRequest;
use PHPUnit\Framework\TestCase;

class ResolveWaypointTest extends TestCase
{
    public function test_resolves_lat_lng_string_in_route_matrix_request()
    {
        $request = new RouteMatrixRequest();
        $request->addOrigin('-6.8105,39.2028');
        $request->addDestination('Dar es Salaam');

        $array = $request->toArray();
        $origin = $array['origins'][0]['waypoint'];

        $this->assertArrayHasKey('location', $origin);
        $this->assertArrayHasKey('latLng', $origin['location']);
        $this->assertEquals(-6.8105, $origin['location']['latLng']['latitude']);
        $this->assertEquals(39.2028, $origin['location']['latLng']['longitude']);
    }

    public function test_resolves_lat_lng_string_in_route_request()
    {
        $request = new RouteRequest();
        $request->from('-6.8105,39.2028');
        $request->to('Dar es Salaam');

        $array = $request->toArray();
        $origin = $array['origin'];

        $this->assertArrayHasKey('location', $origin);
        $this->assertArrayHasKey('latLng', $origin['location']);
        $this->assertEquals(-6.8105, $origin['location']['latLng']['latitude']);
        $this->assertEquals(39.2028, $origin['location']['latLng']['longitude']);
    }

    public function test_resolves_lat_lng_string_with_spaces()
    {
        $request = new RouteRequest();
        $request->from('-6.8105, 39.2028'); // Space after comma
        $request->to('Dar es Salaam');

        $array = $request->toArray();
        $origin = $array['origin'];

        $this->assertEquals(39.2028, $origin['location']['latLng']['longitude']);
    }
}
