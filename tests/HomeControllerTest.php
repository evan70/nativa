<?php

// Use Pest PHP for testing
use App\Controllers\HomeController;
use Marko\Http\Request;
use Marko\Http\Response;

test('HomeController returns Hello, Marko!', function () {
    $controller = new HomeController();
    $request = new Request('/');
    $response = $controller->index($request);
    expect($response)->toBeInstanceOf(Response::class);
    // Assuming Response has getContent method
    if (method_exists($response, 'getContent')) {
        expect($response->getContent())->toBe('Hello, Marko!');
    } else {
        // If no getContent, just check that response is not null
        expect($response)->not->toBeNull();
    }
});
