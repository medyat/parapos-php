<?php

namespace MedyaT\Parapos\Actions;

use Illuminate\Database\Eloquent\Model;
use MedyaT\Parapos\Contracts\PaymentStatus;
use MedyaT\Parapos\Models\Payment;

final class FindOrNewPaymentAction
{
    public function __invoke(int|string|null $payment_id = null): Model
    {
        /** @var class-string<Model> $model */
        $model = config('parapos.model', Payment::class);

        $payment = new $model(['status' => PaymentStatus::PAYMENT_PENDING]);

        if (! is_null($payment_id)) {

            $paymentFromDb = $model::query()
                ->where('id', $payment_id)
                ->where('status', PaymentStatus::PAYMENT_PENDING)->first();

            if (! is_null($paymentFromDb)) {
                $payment = $paymentFromDb;
            }
        }

        $payment->save();

        return $payment;

    }
}
