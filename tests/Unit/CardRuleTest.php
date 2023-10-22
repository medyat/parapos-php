<?php

use MedyaT\Parapos\Rules\CardRule;

it('can test money rule', function () {

    $rule = new CardRule();

    $messageBag = [];

    $fail = function ($message) use (&$messageBag) {
        $messageBag[] = $message;
    };

    $rule->validate('attr', '5549601234567891', $fail);

    expect($messageBag)->toBeEmpty();

    $rule->validate('attr', '5549601234567892', $fail);

    expect($messageBag)
        ->toHaveCount(1)
        ->toContain('Geçerli bir kredi kartı numarası girmelisiniz.');

    $messageBag = [];
});
