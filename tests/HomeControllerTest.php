<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\HomeController;
use Marko\Routing\Http\Request;
use Marko\Routing\Http\Response;
use Marko\View\ViewInterface;
use Mockery;

describe('HomeController', function (): void {
    afterEach(function (): void {
        Mockery::close();
    });

    it('returns welcome response from index', function (): void {
        // Mock the ViewInterface
        $view = Mockery::mock(ViewInterface::class);
        
        $expectedResponse = Response::html('Welcome to Nativa');

        $view->shouldReceive('render')
            ->once()
            ->with('app.home', [
                'eyebrow' => 'Nativa',
                'title' => 'Welcome to Nativa',
                'message' => 'Hello, Marko!',
            ])
            ->andReturn($expectedResponse);

        $controller = new HomeController($view);
        
        // Create a Request
        $request = new Request(
            server: ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/'],
            query: [],
            post: [],
            body: ''
        );

        if (in_array('--verbose', $_SERVER['argv']) || in_array('-v', $_SERVER['argv'])) {
            echo "   LOG: Executing HomeController::index test with mocked dependencies\n";
        }

        $response = $controller->index($request);

        expect($response)->toBe($expectedResponse);
        expect($response->body())->toBe('Welcome to Nativa');
        expect($response->statusCode())->toBe(200);
        expect($response->headers()['Content-Type'])->toContain('text/html');
    });
});
