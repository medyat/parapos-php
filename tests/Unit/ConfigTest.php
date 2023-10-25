<?php

use MedyaT\Parapos\Config\Config;

it('can test config signature', function () {

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

it('can get api url with trailing slash uri', function () {

    $config = new Config([
        'apiUrl' => 'https://example.com/',
    ]);

    expect($config->getApiUrl('/bin/'))
        ->toBeString()
        ->toBe('https://example.com/bin');

});

it('can get marketplace default value', function () {

    $config = new Config();

    expect($config->isMarketplace)
        ->toBeFalse();

});
it('can get marketplace set value', function () {

    $config = new Config(['isMarketplace' => true]);

    expect($config->isMarketplace)
        ->toBeTrue();

});

it('can get api url with no slash uri', function () {

    $config = new Config([
        'apiUrl' => 'https://example.com',
    ]);

    expect($config->getApiUrl('bin'))
        ->toBeString()
        ->toBe('https://example.com/bin');

});

it('can get default test url with no slash uri', function () {

    $config = new Config();

    expect($config->getApiUrl('bin'))
        ->toBeString()
        ->toBe('https://test-api.parapos.com/bin');

});

it('can get default prod url with no slash uri', function () {

    $config = new Config([
        'isTest' => false,
    ]);

    expect($config->getApiUrl('bin'))
        ->toBeString()
        ->toBe('https://api.parapos.com/bin');

});
