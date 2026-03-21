<?php

namespace Gowelle\LaravelRouteMatrix\Tests;

use Gowelle\LaravelRouteMatrix\Exceptions\GoogleRoutesException;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ResiliencyTest extends TestCase
{
    /** @test */
    public function it_retries_on_server_errors_5xx()
    {
        // Mock: 500, 503, then 200 OK
        $mock = new MockHandler([
            new Response(500, [], json_encode(['error' => ['message' => 'Internal Server Error']])),
            new Response(503, [], json_encode(['error' => ['message' => 'Service Unavailable']])),
            new Response(200, [], json_encode(['routes' => []])),
        ]);

        // Inject the mock handler directly.
        // The client will wrap this with its own HandlerStack, including the RetryMiddleware.
        $client = new GoogleRoutesClient(
            handler: $mock,
            maxRetries: 3
        );

        // Make a request. It should fail twice internally and succeed on the third try.
        // We use computeRoutes logic (via from/to wrapper)
        $response = $client->from('Origin')->to('Dest')->get();

        $this->assertNotNull($response);
        // Verify that 3 requests were made (500, 503, 200)
        // MockHandler count() returns number of remaining items.
        // If we consumed 3 responses, count should be 0.
        $this->assertEquals(0, $mock->count());
        $this->assertEquals(2, $mock->getLastRequest()->getHeaderLine('X-Guzzle-Retry') ?? 0);
    }

    /** @test */
    public function it_retries_on_rate_limiting_429()
    {
        // Mock: 429, then 200 OK
        $mock = new MockHandler([
            new Response(429, [], json_encode(['error' => ['message' => 'Quota Exceeded']])),
            new Response(200, [], json_encode(['routes' => []])),
        ]);

        $client = new GoogleRoutesClient(
            handler: $mock,
            maxRetries: 3
        );

        $client->from('Origin')->to('Dest')->get();

        $this->assertEquals(0, $mock->count());
    }

    /** @test */
    public function it_retries_on_connection_exceptions()
    {
        // Mock: ConnectException, then 200 OK
        $mock = new MockHandler([
            new ConnectException('Connection timeout', new Request('POST', 'test')),
            new Response(200, [], json_encode(['routes' => []])),
        ]);

        $client = new GoogleRoutesClient(
            handler: $mock,
            maxRetries: 3
        );

        // This validates that the RetryMiddleware catches the ConnectException
        $client->from('Origin')->to('Dest')->get();

        $this->assertEquals(0, $mock->count());
    }

    /** @test */
    public function it_fails_after_max_retries()
    {
        // Mock: 500, 500, 500, 500 (Max 3 retries = 4 total attempts)
        $mock = new MockHandler([
            new Response(500, [], json_encode(['error' => ['message' => 'Error 1']])),
            new Response(500, [], json_encode(['error' => ['message' => 'Error 2']])),
            new Response(500, [], json_encode(['error' => ['message' => 'Error 3']])),
            new Response(500, [], json_encode(['error' => ['message' => 'Error 4']])),
        ]);

        $client = new GoogleRoutesClient(
            handler: $mock,
            maxRetries: 3
        );

        $this->expectException(GoogleRoutesException::class);

        $client->from('Origin')->to('Dest')->get();
    }
}
