<?php

use MedyaT\Parapos\Actions\FindOrNewPaymentAction;
use MedyaT\Parapos\Contracts\PaymentStatus;
use MedyaT\Parapos\Models\Payment;
use Tests\TestCustomPayment;

beforeEach(function (): void {
    TestCustomPayment::createTable();
});

it('resolves the globally configured model when none is injected', function () {

    config()->set('parapos.model', Payment::class);

    expect((new FindOrNewPaymentAction)())->toBeInstanceOf(Payment::class);

});

it('resolves the injected model over the global config', function () {

    config()->set('parapos.model', Payment::class);

    $payment = (new FindOrNewPaymentAction(TestCustomPayment::class))();

    expect($payment)->toBeInstanceOf(TestCustomPayment::class)
        ->and($payment->exists)->toBeTrue();

});

it('finds an existing pending payment of the injected model', function () {

    config()->set('parapos.model', Payment::class);

    $existing = new TestCustomPayment;
    $existing->status = PaymentStatus::PAYMENT_PENDING;
    $existing->save();

    $found = (new FindOrNewPaymentAction(TestCustomPayment::class))($existing->id);

    expect($found)->toBeInstanceOf(TestCustomPayment::class)
        ->and($found->id)->toBe($existing->id);

});

it('does not leak the injected model into a later default resolution', function () {

    config()->set('parapos.model', Payment::class);

    (new FindOrNewPaymentAction(TestCustomPayment::class))();

    expect((new FindOrNewPaymentAction)())->toBeInstanceOf(Payment::class);

});
