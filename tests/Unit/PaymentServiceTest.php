<?php

use MedyaT\Parapos\Parapos;

it('payment needs to have bin method', function () {

    $parapos = new Parapos(['apiUrl' => 'https://bayi.biz']);

    $payment = $parapos->payment();

    expect($payment)
        ->toHaveMethods(['pay3d']);

});

it('can test pay3d', function () {

    $parapos = new Parapos(['apiUrl' => 'https://testpos.bayi.biz/api', 'secretKey' => '21VEyjUYlsYHnuKo3qCSN2y14vG2Oz7FoS1Sra5x']);

    $parapos = $parapos->payment()
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
            user_id: 1,
            reference_id: 55,
            installment: 2,
            ratio: 1.55
        )
        ->addDealerAmount(
            dealer_id: 1,
            amount: 123.45,
            dealer_commission_amount: 0,
        );

    $response = $parapos->pay3d();

    $db_payment = \MedyaT\Parapos\Models\Payment::find($parapos->payment->id);

    expect($db_payment->status)
        ->toEqual(\MedyaT\Parapos\Models\Payment::PAYMENT_PENDING);

    expect($db_payment->amount)
        ->toEqual(123.45);

    expect($db_payment->installment)
        ->toEqual(2);

    expect($db_payment->ratio)
        ->toEqual(1.55);

    expect($response)
        ->toHaveKeys(['parapos_code', 'url']);

});
