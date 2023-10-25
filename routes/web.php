<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MedyaT\Parapos\Models\Payment;

Route::post('parapos/response/{hash}', function (Request $request, $hash) {

    $payment = Payment::where('response_hash', $hash)->firstOrFail();

    $resultCode = $request->get('result_code');
    $resultMessage = $request->get('result_message');

    $payment->status = $resultCode === 'OK'
        ? Payment::PAYMENT_SUCCESS
        : Payment::PAYMENT_FAIL;

    // Run middleware ???

    $payment->save();

    return view('payment.response', [
        'id' => $payment->id,
        'result_code' => $resultCode,
        'result_message' => $resultMessage,
    ]);

})->name('parapos.response');
