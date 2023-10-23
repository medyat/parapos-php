<?php

namespace MedyaT\Parapos\Middlewares;

use Illuminate\Support\Arr;
use MedyaT\Parapos\Config\HttpRequest;
use MedyaT\Parapos\Models\Payment;

final class PaymentMiddleware
{
    /**
     * @return mixed
     */
    public function __invoke(HttpRequest $request, \Closure $next)
    {

        $request = $this->fillPayment($request);

        return $next($request);

    }

    public function fillPayment(HttpRequest $request): HttpRequest
    {
        $payment_id = Arr::get($request->params, 'payment_id', null);

        /** @var Payment $payment */
        $payment = Payment::query()->where('id', $payment_id)->where('status', Payment::PAYMENT_WAITING)->first();

        if (! $payment) {
            $payment = new Payment(['status' => Payment::PAYMENT_WAITING]);
        }

        //        $payment->last_four = Arr::get($request, 'params.last_four', null);
        //        $payment->currency_code = Arr::get($request, 'params.currency_code', 'TRY');
        //
        //        $payment->user_id = Arr::get($request, 'params.user_id', null);
        //        $payment->reference_id = Arr::get($request, 'params.reference_id', null);
        //
        //        $payment->installment = Arr::get($request, 'params.installment', 1);
        //        $payment->card_code = Arr::get($request, 'params.card_code', null);
        //        $payment->bank_code = Arr::get($request, 'params.bank_code', null);
        //        $payment->is_foreign_card = Arr::get($request, 'params.is_foreign_card', 0);
        //        $payment->ratio = Arr::get($request, 'params.ratio', 0.00);
        //        $payment->amount = Arr::get($request, 'params.amount', 0.00);
        //        $payment->name = Arr::get($request, 'params.name', null);
        //        $payment->three_d_verify_hash = Arr::get($request, 'params.three_d_verify_hash', null);
        //        $payment->ip = Arr::get($request, 'params.ip', null);

        $bin = Arr::get($request->params, 'bin');
        if (is_string($bin) && strlen($bin) > 8) {
            $payment->bin = substr($bin, 0, 8);
        }

        $payment->save();

        Arr::set($request->params, 'payment_id', $payment->id);

        return $request;
    }
}
