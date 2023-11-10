<?php

namespace MedyaT\Parapos\Middlewares;

use MedyaT\Parapos\Config\HttpRequest;
use MedyaT\Parapos\Config\HttpResponse;
use MedyaT\Parapos\Models\Payment;

final class PaymentPay3dMiddleware
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

    public function updatePayment(HttpRequest $request, HttpResponse $response): HttpResponse
    {

        $response_array = $response->toArray();

        if ($response->headers['http_code'] != 200) {
            $response->payment->status = Payment::PAYMENT_FAIL;
            $response->payment->save();

            $message = $response_array['message'] ?? 'Payment failed.';

            throw new \Exception($message);
        }

        // {"result_code":"OK","result_message":"Ba\u015far\u0131l\u0131","data":{"id":323,"url":"https:\/\/service.refmoka.com\/PaymentDealerThreeDProcess?threeDTrxCode=667ae4f8-dec9-4cf4-919a-33f76b49fbef&RedirectType=1","remote_code":null}}
        if (isset($response_array['data']['id'])) {
            $response->payment->parapos_code = $response_array['data']['id'];
            $response->payment->save();
        }

        return $response;

    }

    private function createPayment(HttpRequest $request): HttpRequest
    {

        if (isset($request->params['card_number'])) {
            $request->payment->last_four = substr((string) $request->params['card_number'], -4);
            $request->payment->bin = substr((string) $request->params['card_number'], 0, 6);
        }

        if (isset($request->params['currency_code'])) {
            $request->payment->currency_code = (string) $request->params['currency_code'];
        }

        if (isset($request->params['installment'])) {
            $request->payment->installment = (int) $request->params['installment'];
        }

        if (isset($request->params['amount'])) {
            $request->payment->amount = (float) $request->params['amount'];
        }

        if (isset($request->params['name'])) {
            $request->payment->name = (string) $request->params['name'];
        }

        return $request;
    }
}
