<?php

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Config\Http;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;
use MedyaT\Parapos\Parapos;

it('can generate test api url', function () {

    $parapos = new Parapos();

    expect($parapos->config->isTest)
        ->toBe(true)
        ->and($parapos->config->apiUrl)
        ->toBe('https://test-api.parapos.com');

});

it('can generate production api url', function () {

    $parapos = new Parapos(['isTest' => false]);

    expect($parapos->config->isTest)
        ->toBe(false)
        ->and($parapos->config->apiUrl)
        ->toBe('https://api.parapos.com');

});

it('can generate base api url', function () {

    $parapos = new Parapos(['apiUrl' => 'https://bayi.biz']);

    expect($parapos->config->isTest)
        ->toBe(true)
        ->and($parapos->config->apiUrl)
        ->toBe('https://bayi.biz');

});

it('can get service arguments', function () {

    $parapos = new Parapos([
        'apiKey' => 'api-123',
        'secretKey' => 'secret-123',
    ]);
    $service = $parapos->config;

    expect($service)
        ->toBeInstanceOf(Config::class)
        ->and($service->isTest)
        ->toBe(true)
        ->and($service->apiKey)
        ->toBe('api-123')
        ->and($service->secretKey)
        ->toBe('secret-123')
        ->and($service->language)
        ->toBe('tr')
        ->and($service->apiUrl)
        ->toBe('https://test-api.parapos.com');

});

it('can mock http client', function () {

    $http = Mockery::mock(Http::class);

    $payment = new Payment();
    $payment->save();

    $http->shouldReceive('get')
        ->with($payment, 'https://api.parapos.com')
        ->andReturn(new HttpResponse($payment, 'test'));

    $config = new Config();

    $service = new \MedyaT\Parapos\Services\PaymentService($config, $http);

    $value = $service->http->get($payment, 'https://api.parapos.com');

    expect($value)
        ->toBeInstanceOf(HttpResponse::class)
        ->toEqual('test');

});

it('can test service signature', function () {

    $service = new Config([
        'secretKey' => 'A1',
    ]);

    $signature = $service->signature('https://api.parapos.com', 'hash');

    expect($signature)
        ->toBeString()
        ->toBe(
            hash_hmac('sha256', 'https://api.parapos.com'.'hash', 'A1')
        );

});
