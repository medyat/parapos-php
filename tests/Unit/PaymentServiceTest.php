<?php

use MedyaT\Parapos\Parapos;

it('payment needs to have bin method', function () {

    $parapos = new Parapos(['apiUrl' => 'https://bayi.biz']);

    $payment = $parapos->payment();

    expect($payment)
        ->toHaveMethods(['pay', 'pay_3d']);

});
