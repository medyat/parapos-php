<?php

use MedyaT\Parapos\Rules\MoneyRule;

it('can test money rule', function () {

    $rule = new MoneyRule();

    $messageBag = [];

    $fail = function ($message) use (&$messageBag) {
        $messageBag[] = $message;
    };

    $rule->validate('attr', 123.45, $fail);
    $rule->validate('attr', 123, $fail);
    $rule->validate('attr', '123.98', $fail);

    expect($messageBag)->toBeEmpty();

    $rule->validate('attr', '123.982', $fail);

    expect($messageBag)
        ->toHaveCount(1)
        ->toContain(':attribute alanı için para formatında bir değer giriniz.');

    $rule->validate('attr', 123.982, $fail);

    expect($messageBag)->toHaveCount(2);

    $rule->validate('attr', '123,92', $fail);

    expect($messageBag)->toHaveCount(3);
});
