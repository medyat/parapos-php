<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\Http;
use MedyaT\Parapos\Services\PaymentService;

it('can mock http client', function () {

    $http = Mockery::mock(Http::class);

    $payment = new \MedyaT\Parapos\Models\Payment();
    $payment->save();

    $http->shouldReceive('get')
        ->with($payment, 'https://api.parapos.com')
        ->andReturn(new \MedyaT\Parapos\Config\HttpResponse($payment, 'test'));

    $config = new Config();

    $service = new PaymentService($config, $http);

    $value = $service->http->get($payment, 'https://api.parapos.com');

    expect($value)
        ->toBeInstanceOf(\MedyaT\Parapos\Config\HttpResponse::class)
        ->toEqual('test');

});

it('can add middleware string', function () {

    $service = new PaymentService(new Config());

    expect($service->http->middlewares)
        ->toBeArray()
        ->toHaveCount(0);

    $service->middleware(\Tests\TestMiddleware::class);

    expect($service->http->middlewares)
        ->toBeArray()
        ->toHaveCount(1);

});

it('can add middleware closure', function () {

    $service = new PaymentService(new Config());

    expect($service->http->middlewares)
        ->toBeArray()
        ->toHaveCount(0);

    $service->middleware(function ($request, Closure $next) {
        $request['params']['test_request_2'] = 'test_request_2';
        $response = $next($request);
        $response .= ':test_response_2';

        return $response;
    });

    expect($service->http->middlewares)
        ->toBeArray()
        ->toHaveCount(1);

});
