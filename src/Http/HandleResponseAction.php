<?php

namespace MedyaT\Parapos\Http;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View as ViewFactory;
use MedyaT\Parapos\Contracts\PaymentStatus;
use MedyaT\Parapos\Exceptions\VerifyResponseMiddlewareShouldBeImplemented;
use MedyaT\Parapos\Middlewares\VerifyResponseMiddlewareInterface;
use MedyaT\Parapos\Models\Payment;

/**
 * The bank's 3D callback handler. Extracted from the bundled route so a host
 * app can register a second callback — its own URI, its own route middleware,
 * its own payment model — by extending this class and overriding the accessors
 * below. That keeps two profiles apart without writing to global config, which
 * would leak between requests in a long-lived worker.
 */
class HandleResponseAction
{
    public function __invoke(Request $request, string $hash, ?string $tenant = null): View
    {
        /*
         * Typed as the default model for static analysis only: a host model may be
         * any class, but the trait and contract oblige it to carry the same shape.
         */
        /** @var Payment $payment */
        $payment = $this->resolvePayment($hash);

        $resultCode = $request->get('result_code');
        $resultMessage = $request->get('result_message');

        if ($resultCode === 'OK' && $payment->is_pre_auth) {
            $payment->status = PaymentStatus::PAYMENT_PRE_AUTHORIZED;
        } elseif ($resultCode === 'OK') {
            $payment->status = PaymentStatus::PAYMENT_SUCCESS;
        } else {
            $payment->status = PaymentStatus::PAYMENT_FAIL;
        }

        foreach ($this->responseMiddlewares() as $middlewareClass) {
            $middleware = new $middlewareClass;

            if (! $middleware instanceof VerifyResponseMiddlewareInterface) {
                throw new VerifyResponseMiddlewareShouldBeImplemented;
            }

            $middleware($request, $payment);
        }

        $payment->save();

        $finalResultCode = match ($payment->status) {
            PaymentStatus::PAYMENT_SUCCESS, PaymentStatus::PAYMENT_PRE_AUTHORIZED => 'OK',
            default => 'FAIL',
        };

        return ViewFactory::make($this->view(), [
            'id' => $payment->id,
            'hash' => $payment->response_hash,
            'result_code' => $finalResultCode,
            'result_message' => $payment->result_message ?? $resultMessage,
        ]);
    }

    protected function resolvePayment(string $hash): Model
    {
        $model = $this->model();

        /** @var Model $payment */
        $payment = $model::query()
            ->where('response_hash', $hash)
            ->where('status', PaymentStatus::PAYMENT_PENDING)
            ->firstOrFail();

        return $payment;
    }

    /**
     * @return class-string<Model>
     */
    protected function model(): string
    {
        /** @var class-string<Model> $model */
        $model = config('parapos.model', Payment::class);

        return $model;
    }

    /**
     * @return array<int, class-string>
     */
    protected function responseMiddlewares(): array
    {
        /** @var array<int, class-string> $middlewares */
        $middlewares = config('parapos.response_middlewares', []);

        return $middlewares;
    }

    protected function view(): string
    {
        /** @var string $view */
        $view = config('parapos.view', 'parapos::response');

        return $view;
    }
}
