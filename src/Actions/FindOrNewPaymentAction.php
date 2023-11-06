<?php

namespace MedyaT\Parapos\Actions;

use MedyaT\Parapos\Models\Payment;

final class FindOrNewPaymentAction
{
    public function __invoke(int $payment_id = null): Payment
    {

        $payment = new Payment(['status' => Payment::PAYMENT_WAITING]);

        if (! is_null($payment_id)) {

            $paymentFromDb = Payment::query()
                ->where('id', $payment_id)
                ->where('status', Payment::PAYMENT_WAITING)->first();

            if (! is_null($paymentFromDb)) {
                /** @var Payment $payment */
                $payment = $paymentFromDb;
            }
        }

        $payment->save();

        return $payment;

    }
}
