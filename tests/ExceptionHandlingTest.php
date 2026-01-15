<?php

namespace Gowelle\LaravelRouteMatrix\Tests;

use Gowelle\LaravelRouteMatrix\Exceptions\OverQueryLimitException;
use Gowelle\LaravelRouteMatrix\Exceptions\RequestDeniedException;
use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class ExceptionHandlingTest extends TestCase
{
    /** @test */
    public function it_throws_over_query_limit_exception_on_429()
    {
        // Mock a 429 response
        $mock = new MockHandler([
            new Response(429, [], json_encode(['error' => ['message' => 'Quota exceeded']])),
        ]);

        $client = new GoogleRoutesClient(handler: $mock);

        $this->expectException(OverQueryLimitException::class);
        $this->expectExceptionMessage('Quota exceeded');

        // We need to trigger a request. The exact request doesn't matter as the mock returns 429 immediately.
        $client->from('Origin')->to('Destination')->get();
    }

    /** @test */
    public function it_throws_request_denied_exception_on_403()
    {
        // Mock a 403 response
        $mock = new MockHandler([
            new Response(403, [], json_encode(['error' => ['message' => 'Permission denied']])),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new GoogleRoutesClient(handler: $mock);

        $this->expectException(RequestDeniedException::class);
        $this->expectExceptionMessage('Permission denied');

        $client->from('Origin')->to('Destination')->get();
    }
}
