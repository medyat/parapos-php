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

    $config = new Config;

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

    $config = new Config;

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

it('prefers the instance response_url over global config', function () {

    config()->set('parapos.response_url', 'global/response/{hash}/{tenant?}');
    config()->set('app.url', 'https://example.com');

    $config = new Config([
        'response_url' => 'instance/response/{hash}',
    ]);

    expect($config->getResponseUrl('abc'))
        ->toBe('https://example.com/instance/response/abc');

});

it('prefers the instance tenant over global config', function () {

    config()->set('parapos.tenant', 'global-tenant');
    config()->set('app.url', 'https://example.com');

    $config = new Config([
        'tenant' => 'instance-tenant',
    ]);

    expect($config->getResponseUrl('abc'))
        ->toBe('https://example.com/parapos/response/abc/instance-tenant');

});

it('prefers the instance appUrl over global config', function () {

    config()->set('app.url', 'https://global.example.com');

    $config = new Config([
        'appUrl' => 'https://instance.example.com/',
        'tenant' => null,
    ]);

    expect($config->getResponseUrl('abc'))
        ->toBe('https://instance.example.com/parapos/response/abc');

});

it('falls back to global config when no instance values are given', function () {

    config()->set('parapos.response_url', 'parapos/response/{hash}/{tenant?}');
    config()->set('parapos.tenant', 'global-tenant');
    config()->set('app.url', 'https://global.example.com');

    $config = new Config;

    expect($config->getResponseUrl('abc'))
        ->toBe('https://global.example.com/parapos/response/abc/global-tenant');

});

it('drops the tenant segment when neither instance nor config supplies one', function () {

    config()->set('parapos.tenant', null);
    config()->set('app.url', 'https://example.com');

    $config = new Config;

    expect($config->getResponseUrl('abc'))
        ->toBe('https://example.com/parapos/response/abc');

});

it('carries the payment model on the instance', function () {

    expect((new Config)->model)->toBeNull();

    expect((new Config(['model' => \Tests\TestCustomPayment::class]))->model)
        ->toBe(\Tests\TestCustomPayment::class);

});

it('lets an explicit null tenant opt out of a globally configured one', function () {

    config()->set('parapos.tenant', 'global-tenant');
    config()->set('app.url', 'https://example.com');

    /*
     * An admin profile running beside a tenant profile: no tenant segment at
     * all. A null property alone cannot be told apart from an absent one, so
     * this only works because the constructor records that the key was passed.
     */
    $config = new Config(['tenant' => null]);

    expect($config->getResponseUrl('abc'))
        ->toBe('https://example.com/parapos/response/abc');

});

it('still reads the global tenant when the key is absent entirely', function () {

    config()->set('parapos.tenant', 'global-tenant');
    config()->set('app.url', 'https://example.com');

    /* Same object, but the key is omitted rather than nulled — global wins. */
    $config = new Config(['appUrl' => 'https://example.com']);

    expect($config->getResponseUrl('abc'))
        ->toBe('https://example.com/parapos/response/abc/global-tenant');

});
