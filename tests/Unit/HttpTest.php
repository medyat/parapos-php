<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\Http;
use MedyaT\Parapos\Config\HttpRequest;
use MedyaT\Parapos\Config\HttpResponse;
use Tests\TestMiddleware;

it('test constructor injection', function () {

    $service = new Config();
    $httpClient = new Http($service);

    expect($httpClient)
        ->toBeInstanceOf(Http::class)
        ->and($httpClient->config)
        ->toBe($service);

});

it('test get request', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config()]);

    $url = 'https://example.com';

    $httpClient
        ->shouldReceive('call')
        ->with($url, 'GET', [], [])
        ->andReturn($httpResponse = new HttpResponse('response'));

    $response = $httpClient->get($url);

    expect($response)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual($httpResponse);

    // Don't forget to release the mock
    Mockery::close();

});

it('test post request', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config()]);

    $url = 'https://example.com';

    $httpClient
        ->shouldReceive('call')
        ->with($url, 'POST', ['x-header' => 'value'], ['key' => 'value'])
        ->andReturn($httpResponse = new HttpResponse('response'));

    $response = $httpClient->post($url, ['key' => 'value'], ['x-header' => 'value']);

    expect($response)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual($httpResponse);

    // Don't forget to release the mock
    Mockery::close();
});

it('test put request', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config()]);

    $url = 'https://example.com';

    $httpClient
        ->shouldReceive('call')
        ->with($url, 'PUT', [], ['key' => 'value'])
        ->andReturn($httpResponse = new HttpResponse('response'));

    $response = $httpClient->put($url, ['key' => 'value']);

    expect($response)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual($httpResponse);

    // Don't forget to release the mock
    Mockery::close();
});

it('test delete request', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config()]);

    $url = 'https://example.com';

    $httpClient
        ->shouldReceive('call')
        ->with($url, 'DELETE', [], [])
        ->andReturn($httpResponse = new HttpResponse('response'));

    $response = $httpClient->delete($url);

    expect($response)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual($httpResponse);
    // Don't forget to release the mock
    Mockery::close();

});

it('test add middleware method', function () {

    $httpClient = new Http(new Config());

    $httpClient->addMiddleware(function ($request, $next) {
        return $next($request);
    });

    expect($httpClient->middlewares)
        ->toBeArray()
        ->toHaveCount(1);

    expect($httpClient->middlewares[0])
        ->toBeInstanceOf(Closure::class)
        ->toBeCallable();

    $httpClient->addMiddleware(PaymentMiddleware::class);
    $httpClient->addMiddleware(PaymentMiddleware::class);

    expect($httpClient->middlewares)
        ->toBeArray()
        ->toHaveCount(2);

});

it('test middleware closure', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config()]);

    $url = 'https://example.com';

    $httpClient
        ->shouldReceive('call')
        ->with($url, 'POST', [], ['key' => 'request', 'test_request_1' => 'test_request_1', 'test_request_2' => 'test_request_2'])
        ->andReturn($httpResponse = new HttpResponse('response'));

    $httpClient->addMiddleware(function (HttpRequest $request, Closure $next) {
        $request->params['test_request_1'] = 'test_request_1';
        /** @var HttpResponse $response */
        $response = $next($request);
        $response->response .= ':test_response_1';

        return $response;
    });

    $httpClient->addMiddleware(function (HttpRequest $request, Closure $next) {
        $request->params['test_request_2'] = 'test_request_2';
        /** @var HttpResponse $response */
        $response = $next($request);
        $response->response .= ':test_response_2';

        return $response;
    });

    $response = $httpClient->post('https://example.com', ['key' => 'request']);

    expect($response)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual('response:test_response_1:test_response_2');

});

it('test middleware class', function () {

    $httpClient = Mockery::mock('\MedyaT\Parapos\Config\Http[call]', [new Config()]);

    $url = 'https://example.com';

    $httpClient
        ->shouldReceive('call')
        ->with($url, 'POST', [], ['key' => 'request', 'test_request_1' => 'test_request_1'])
        ->andReturn($httpResponse = new HttpResponse('response'));

    $httpClient->addMiddleware(TestMiddleware::class);

    $response = $httpClient->post('https://example.com', ['key' => 'request']);

    expect($response)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual('response:test_response_1');

});
