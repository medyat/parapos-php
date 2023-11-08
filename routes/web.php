<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use MedyaT\Parapos\Exceptions\VerifyResponseMiddlewareShouldBeImplemented;
use MedyaT\Parapos\Middlewares\VerifyResponseMiddlewareInterface;
use MedyaT\Parapos\Models\Payment;

Route::post('parapos/response/{hash}/{tenant?}', function (Request $request, $hash, $tenant = null) {

    $payment = Payment::where('response_hash', $hash)->firstOrFail();

    $resultCode = $request->get('result_code');
    $resultMessage = $request->get('result_message');

    $payment->status = $resultCode === 'OK'
        ? Payment::PAYMENT_SUCCESS
        : Payment::PAYMENT_FAIL;

    $middlewares = config('parapos.response_middlewares', []);

    foreach ($middlewares as $middleware) {
        $middleware = new $middleware;
        if (! in_array(VerifyResponseMiddlewareInterface::class, class_implements($middleware))) {
            throw new VerifyResponseMiddlewareShouldBeImplemented();
        }
        $middleware($request, $payment);
    }

    $payment->save();

    return view(config('parapos.view', 'parapos::response'), [
        'id' => $payment->id,
        'hash' => $payment->response_hash,
        'result_code' => $resultCode,
        'result_message' => $resultMessage,
    ]);

})
    ->middleware('parapos-middleware')
    ->name('parapos.response');
