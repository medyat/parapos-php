<?php

use MedyaT\Parapos\Parapos;

it('payment needs to have bin method', function () {

    $parapos = new Parapos(['apiUrl' => 'https://bayi.biz']);

    $payment = $parapos->payment();

    expect($payment)
        ->toHaveMethods(['pay3d']);

});

it('can test pay3d', function () {

    $parapos = new Parapos(['apiUrl' => 'https://testpos.bayi.biz/api', 'secretKey' => '']);

    $response = $parapos->payment()
        ->addCard(
            card_number: '5269 5511 2222 3339',
            name: 'John Doe',
            cvv2: '123',
            expire_date_month: '12',
            expire_date_year: '2025'
        )
        ->addPayment(
            client_ip: '127.0.0.1',
            amount: 123.45,
            installment: 1,
        )
        ->addDealerAmount(
            dealer_id: 1,
            amount: 123.45,
            dealer_commission_amount: 0,
        )
        ->pay3d();

    expect($response)
        ->toHaveKeys(['parapos_code', 'url']);
});
