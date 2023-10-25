<?php

namespace MedyaT\Parapos\Middlewares;

use Illuminate\Support\Arr;
use MedyaT\Parapos\Actions\FindOrNewPaymentAction;
use MedyaT\Parapos\Config\HttpRequest;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;

final class PaymentMiddleware
{
    /**
     * @return mixed
     */
    public function __invoke(HttpRequest $request, \Closure $next)
    {

        $request = $this->createPayment($request);

        $response = $next($request);

        return $this->updatePayment($request, $response);

    }

    public function createPayment(HttpRequest $request): HttpRequest
    {

        // if there is no payment_id in params, create a new payment
        $payment_id = Arr::get($request->params, 'payment_id', null);

        $payment = (new FindOrNewPaymentAction())($payment_id);

        //        $payment->last_four = Arr::get($request, 'params.last_four', null);
        //        $payment->currency_code = Arr::get($request, 'params.currency_code', 'TRY');

        //        $payment->user_id = Arr::get($request, 'params.user_id', null);
        //        $payment->reference_id = Arr::get($request, 'params.reference_id', null);

        //        $payment->installment = Arr::get($request, 'params.installment', 1);
        //        $payment->card_code = Arr::get($request, 'params.card_code', null);
        //        $payment->bank_code = Arr::get($request, 'params.bank_code', null);
        //        $payment->is_foreign_card = Arr::get($request, 'params.is_foreign_card', 0);
        //        $payment->ratio = Arr::get($request, 'params.ratio', 0.00);
        //        $payment->amount = Arr::get($request, 'params.amount', 0.00);
        //        $payment->name = Arr::get($request, 'params.name', null);
        //        $payment->three_d_verify_hash = Arr::get($request, 'params.three_d_verify_hash', null);
        //        $payment->ip = Arr::get($request, 'params.ip', null);

        if (isset($request->params['bin'])) {
            $payment->bin = (string) $request->params['bin'];
        }

        if (isset($request->params['amount'])) {
            $payment->amount = (float) $request->params['amount'];
        }

        $payment->save();

        $request->payment = $payment;

        Arr::set($request->params, 'payment_id', $payment->id);

        return $request;
    }

    public function updatePayment(HttpRequest $request, HttpResponse $response): HttpResponse
    {

        $response->payment = $request->payment;

        return $response;

    }
}
