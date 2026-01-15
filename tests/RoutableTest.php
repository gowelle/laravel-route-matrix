<?php

namespace Gowelle\LaravelRouteMatrix\Tests;

use Gowelle\LaravelRouteMatrix\Contracts\Routable;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use Gowelle\LaravelRouteMatrix\RouteRequest;
use Gowelle\LaravelRouteMatrix\Traits\HasRoute;
use Gowelle\LaravelRouteMatrix\ValueObjects\Waypoint;
use Illuminate\Database\Eloquent\Model;

class RoutableTest extends TestCase
{
    /** @test */
    public function it_can_create_waypoint_from_routable_with_lat_lng_attributes()
    {
        $routable = new class extends Model implements Routable
        {
            use HasRoute;

            protected $attributes = [
                'lat' => 1.23,
                'lng' => 4.56,
            ];
        };

        $waypoint = $routable->getWaypoint();

        $this->assertInstanceOf(Waypoint::class, $waypoint);
        // We can't easily check private properties of Waypoint, but we can check its toArray output
        $array = $waypoint->toArray();
        $this->assertEquals(['location' => ['latLng' => ['latitude' => 1.23, 'longitude' => 4.56]]], $array);
    }

    /** @test */
    public function it_can_create_waypoint_from_routable_with_address()
    {
        $routable = new class extends Model implements Routable
        {
            use HasRoute;

            protected $attributes = [
                'address' => '1600 Amphitheatre Parkway, Mountain View, CA',
            ];
        };

        $waypoint = $routable->getWaypoint();

        $this->assertInstanceOf(Waypoint::class, $waypoint);
        $this->assertEquals(['address' => '1600 Amphitheatre Parkway, Mountain View, CA'], $waypoint->toArray());
    }

    /** @test */
    public function it_can_pass_routable_to_client_from()
    {
        $routable = new class extends Model implements Routable
        {
            use HasRoute;

            protected $attributes = [
                'lat' => 10.0,
                'lng' => 20.0,
            ];
        };

        // We use the real client but don't execute the request
        /** @var GoogleRoutesClient $client */
        $client = app(GoogleRoutesClient::class);

        $request = $client->from($routable);

        $this->assertInstanceOf(RouteRequest::class, $request);
    }
}
