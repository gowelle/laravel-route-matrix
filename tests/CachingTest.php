<?php

namespace Gowelle\LaravelRouteMatrix\Tests;

use Gowelle\LaravelRouteMatrix\DataTransferObjects\RoutesResponse;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Mockery;

class CachingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure mock environment is clean
        Mockery::close();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_returns_cached_response_and_skips_api_call_on_hit()
    {
        // 1. Mock Cache: Returns data (hit)
        $cachedData = ['routes' => [['legs' => []]]];
        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('get')->andReturn($cachedData);
        $cache->shouldNotReceive('put');

        // 2. Mock API: Should NOT be called.
        $mock = new MockHandler([]); // Empty queue

        // 3. Enable Cache Config
        config(['google-routes.cache.enabled' => true]);

        $client = new GoogleRoutesClient(
            handler: $mock,
            cache: $cache
        );

        // If it calls API, Guzzle will throw Exception because queue is empty
        $response = $client->from('A')->to('B')->get();

        $this->assertInstanceOf(RoutesResponse::class, $response);
    }

    /** @test */
    public function it_fetches_from_api_and_caches_response_on_miss()
    {
        // 1. Mock Cache: Returns null (miss), then expect put
        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldReceive('get')->andReturn(null);
        $cache->shouldReceive('put')->once()->with(
            Mockery::type('string'),
            Mockery::type('array'),
            Mockery::type('int')
        );

        // 2. Mock API: Returns success
        $mock = new MockHandler([
            new Response(200, [], json_encode(['routes' => [['legs' => []]]])),
        ]);

        // 3. Enable Cache Config
        config(['google-routes.cache.enabled' => true]);

        $client = new GoogleRoutesClient(
            handler: $mock,
            cache: $cache
        );

        $response = $client->from('A')->to('B')->get();

        $this->assertInstanceOf(RoutesResponse::class, $response);
    }

    /** @test */
    public function it_does_not_use_cache_if_disabled_in_config()
    {
        // 1. Mock Cache: Should NOT be touched even if passed
        $cache = Mockery::mock(CacheRepository::class);
        $cache->shouldNotReceive('get');
        $cache->shouldNotReceive('put');

        // 2. Mock API: Returns success
        $mock = new MockHandler([
            new Response(200, [], json_encode(['routes' => [['legs' => []]]])),
        ]);

        // 3. Disable Cache Config
        config(['google-routes.cache.enabled' => false]);

        $client = new GoogleRoutesClient(
            handler: $mock,
            cache: $cache
        );

        $response = $client->from('A')->to('B')->get();

        $this->assertInstanceOf(RoutesResponse::class, $response);
    }
}
