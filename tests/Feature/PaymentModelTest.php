<?php

test('confirm environment is set to testing', function () {
    expect(config('app.env'))->toBe('testing');
});

it('can create payment', function () {

    $payment = new \MedyaT\Parapos\Models\Payment();

    $payment->parapos_code = 'ok';

    expect($payment->response_hash)->toBeNull();

    $payment->save();

    expect($payment->exists())->toBeTrue();

    expect($payment->response_hash)
        ->toBeString()
        ->toHaveLength(20);

});
