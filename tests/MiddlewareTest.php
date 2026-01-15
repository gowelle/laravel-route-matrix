<?php

namespace Gowelle\LaravelRouteMatrix\Tests;

use Gowelle\LaravelRouteMatrix\GoogleRoutesClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

class MiddlewareTest extends TestCase
{
    /** @test */
    public function it_executes_injected_middleware()
    {
        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['routes' => []])),
        ]);

        $stack = HandlerStack::create($mock);
        $stack->push($history);

        // We can inject a middleware that adds a header
        $addHeaderMiddleware = function (callable $handler) {
            return function ($request, $options) use ($handler) {
                $request = $request->withHeader('X-Test-Middleware', 'true');

                return $handler($request, $options);
            };
        };

        // Note: We are testing that the client accepts *our* middleware array.
        // But we also need to pass the MockHandler so no real request is made.
        // We pass the MockHandler as the 'handler' argument we just added.

        $client = new GoogleRoutesClient(
            apiKey: 'test-key',
            middleware: ['add_header' => $addHeaderMiddleware],
            handler: $mock
        );

        // Wait, if we pass $mock to `handler`, `GoogleRoutesClient` creates a NEW HandlerStack with it.
        // But if we want to capture history, we need the history middleware to be in the stack too.
        // We can pass the history middleware in the `middleware` array!

        $client = new GoogleRoutesClient(
            apiKey: 'test-key',
            middleware: [
                'add_header' => $addHeaderMiddleware,
                'history' => $history,
            ],
            handler: $mock
        );

        // Let's use the simplest public method that triggers a request.
        try {
            $client->computeRoutes(
                $client->from('New York')->to('Los Angeles')
            );
        } catch (\Exception $e) {
            // Ignore format errors if mock response is simple
        }

        $this->assertCount(1, $container);
        $request = $container[0]['request'];

        $this->assertTrue($request->hasHeader('X-Test-Middleware'));
        $this->assertEquals('true', $request->getHeaderLine('X-Test-Middleware'));
    }
}
