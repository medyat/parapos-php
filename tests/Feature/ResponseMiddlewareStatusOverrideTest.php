<?php

use Illuminate\Http\Request;
use MedyaT\Parapos\Middlewares\VerifyResponseMiddlewareInterface;
use MedyaT\Parapos\Models\Payment;

/**
 * Middleware that simulates an issuer decline at capture: 3DS authentication
 * succeeded (route already set PAYMENT_SUCCESS / PAYMENT_PRE_AUTHORIZED from
 * the inbound result_code=OK), but the explicit pay3dComplete / post_auth call
 * inside the middleware reveals the issuer refused the transaction, so the
 * middleware downgrades the status to PAYMENT_FAIL.
 */
class DowngradeToFailMiddleware implements VerifyResponseMiddlewareInterface
{
    public function __invoke(Request $request, Payment $payment)
    {
        $payment->status = Payment::PAYMENT_FAIL;
    }
}

class DowngradeToFailWithMessageMiddleware implements VerifyResponseMiddlewareInterface
{
    public function __invoke(Request $request, Payment $payment)
    {
        $payment->status = Payment::PAYMENT_FAIL;
        $payment->result_message = 'Kart Sahibine Açık Olmayan İşlem';
    }
}

class NoopResponseMiddleware implements VerifyResponseMiddlewareInterface
{
    public function __invoke(Request $request, Payment $payment) {}
}

beforeEach(function () {
    config()->set('parapos.route_middlewares', []);
    app()->make('router')->middlewareGroup('parapos-middleware', config('parapos.route_middlewares'));
});

it('emits result_code=FAIL to the view when a response middleware downgrades a SUCCESS to FAIL', function () {

    config()->set('parapos.response_middlewares', [DowngradeToFailMiddleware::class]);

    $payment = new Payment;
    $payment->response_hash = 'downgrade-success-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success',
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::PAYMENT_FAIL);

    // The view is what gets postMessage'd to the parent window. It MUST reflect
    // the final payment status, not the stale inbound 3DS-auth result_code.
    $response->assertSee("'result_code': 'FAIL'", false);
    $response->assertDontSee("'result_code': 'OK'", false);
});

it('emits result_code=FAIL to the view when a response middleware downgrades a PRE_AUTHORIZED to FAIL', function () {

    config()->set('parapos.response_middlewares', [DowngradeToFailMiddleware::class]);

    $payment = new Payment;
    $payment->response_hash = 'downgrade-preauth-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->is_pre_auth = 1;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success',
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::PAYMENT_FAIL);

    $response->assertSee("'result_code': 'FAIL'", false);
    $response->assertDontSee("'result_code': 'OK'", false);
});

it('still emits result_code=OK when a response middleware leaves SUCCESS intact', function () {

    config()->set('parapos.response_middlewares', [NoopResponseMiddleware::class]);

    $payment = new Payment;
    $payment->response_hash = 'noop-success-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success',
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::PAYMENT_SUCCESS);

    $response->assertSee("'result_code': 'OK'", false);
});

it('still emits result_code=OK when a response middleware leaves PRE_AUTHORIZED intact', function () {

    config()->set('parapos.response_middlewares', [NoopResponseMiddleware::class]);

    $payment = new Payment;
    $payment->response_hash = 'noop-preauth-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->is_pre_auth = 1;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success',
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::PAYMENT_PRE_AUTHORIZED);

    $response->assertSee("'result_code': 'OK'", false);
});

it('emits the middleware-supplied result_message when a response middleware sets one', function () {

    config()->set('parapos.response_middlewares', [DowngradeToFailWithMessageMiddleware::class]);

    $payment = new Payment;
    $payment->response_hash = 'downgrade-with-message-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Success', // stale inbound 3DS-auth message — must be overridden
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::PAYMENT_FAIL);
    expect($payment->result_message)->toBe('Kart Sahibine Açık Olmayan İşlem');

    $response->assertSee("'result_code': 'FAIL'", false);
    $response->assertSee("'result_message': 'Kart Sahibine Açık Olmayan İşlem'", false);
    $response->assertDontSee("'result_message': 'Success'", false);
});

it('falls back to the inbound result_message when no middleware sets one', function () {

    config()->set('parapos.response_middlewares', [NoopResponseMiddleware::class]);

    $payment = new Payment;
    $payment->response_hash = 'inbound-message-fallback-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'OK',
        'result_message' => 'Authorized at issuer',
    ])->assertOk();

    $response->assertSee("'result_message': 'Authorized at issuer'", false);
});

it('still emits result_code=FAIL on an inbound failure with no response middlewares', function () {

    config()->set('parapos.response_middlewares', []);

    $payment = new Payment;
    $payment->response_hash = 'inbound-fail-hash';
    $payment->status = Payment::PAYMENT_PENDING;
    $payment->save();

    $response = $this->post('/parapos/response/'.$payment->response_hash, [
        'result_code' => 'FAIL',
        'result_message' => 'Issuer decline',
    ])->assertOk();

    $payment->refresh();
    expect($payment->status)->toBe(Payment::PAYMENT_FAIL);

    $response->assertSee("'result_code': 'FAIL'", false);
});
