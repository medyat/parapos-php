<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MedyaT\Parapos\Actions\FindOrNewPaymentAction;
use MedyaT\Parapos\Config\Service;
use MedyaT\Parapos\DataObjects\CardDataObject;
use MedyaT\Parapos\DataObjects\DealerAmountDataObject;
use MedyaT\Parapos\Exceptions\NoCreditCardDefined;
use MedyaT\Parapos\Exceptions\NoPaymentDefined;
use MedyaT\Parapos\Middlewares\PaymentPay3dMiddleware;

final class PaymentService extends Service
{
    public Model $payment;

    public CardDataObject $card;

    /** @var DealerAmountDataObject[] */
    public array $dealer_amounts = [];

    /**
     * Start a 3D payment. Flags compose orthogonally:
     *   is_pre_auth = false, auto_complete = true  -> legacy 3DPay (verify + charge)
     *   is_pre_auth = false, auto_complete = false -> 3D Model (verify, then pay3dComplete)
     *   is_pre_auth = true,  auto_complete = true  -> 3DPay-style pre-auth (verify + hold), then pay3dPostAuth
     *   is_pre_auth = true,  auto_complete = false -> two-step pre-auth (verify, complete=hold, then post_auth)
     *
     * @return array{parapos_code: mixed, url: mixed}
     */
    public function pay3dInit(): array
    {
        if (! isset($this->card)) {
            throw new NoCreditCardDefined;
        }

        if (! isset($this->payment)) {
            throw new NoPaymentDefined;
        }

        return $this->pay3dRequest('pay_3d_init');
    }

    /**
     * Complete a 3D-Model payment (auto_complete = false). Charges the card after
     * the customer has finished 3D verification.
     *
     * @return array{result_code: mixed, result_message: mixed}
     */
    public function pay3dComplete(): array
    {
        if (! isset($this->payment)) {
            throw new NoPaymentDefined;
        }

        $params = [
            'payment_id' => $this->payment->parapos_code,
        ];

        $result = $this->http->post(payment: $this->payment, uri: 'pay_3d_complete', params: $params)->toArray();

        return [
            'result_code' => Arr::get($result, 'result_code'),
            'result_message' => Arr::get($result, 'result_message'),
        ];
    }

    /**
     * @return array{result_code: mixed, result_message: mixed}
     */
    public function pay3dPostAuth(): array
    {
        if (! isset($this->payment)) {
            throw new NoPaymentDefined;
        }

        $params = [
            'payment_id' => $this->payment->parapos_code,
        ];

        if (! empty($this->dealer_amounts)) {
            $params['sub_dealers'] = $this->dealer_amounts;
        }

        $result = $this->http->post(payment: $this->payment, uri: 'pay_3d_post_auth', params: $params)->toArray();

        return [
            'result_code' => Arr::get($result, 'result_code'),
            'result_message' => Arr::get($result, 'result_message'),
        ];
    }

    /**
     * @return array{status: int, result_code: mixed, result_message: mixed}
     */
    public function checkStatus(): array
    {
        if (! isset($this->payment)) {
            throw new NoPaymentDefined;
        }

        $params = [
            'client_order_id' => $this->payment->request_code ?? $this->payment->response_hash,
        ];

        $result = $this->http->post(payment: $this->payment, uri: 'payment_status', params: $params)->toArray();

        $status = (int) Arr::get($result, 'data.status');

        $this->payment->status = $status;
        $this->payment->save();

        return [
            'status' => $status,
            'result_code' => Arr::get($result, 'result_code'),
            'result_message' => Arr::get($result, 'result_message'),
        ];
    }

    /**
     * @return array{parapos_code: mixed, url: mixed}
     */
    private function pay3dRequest(string $uri): array
    {
        $params = [
            'card_number' => $this->card->card_number,
            'name' => $this->card->name,
            'cvv2' => $this->card->cvv2,
            'expire_date_month' => $this->card->expire_date_month,
            'expire_date_year' => $this->card->expire_date_year,

            'installment' => $this->payment->installment,
            'amount' => $this->payment->amount,
            'client_ip' => $this->payment->ip,
            'client_order_id' => $this->payment->request_code ?? $this->payment->response_hash,
            'success_url' => $this->config->getResponseUrl($this->payment->response_hash),
            'fail_url' => $this->config->getResponseUrl($this->payment->response_hash),
            'is_pre_auth' => (int) $this->payment->is_pre_auth,
            'auto_complete' => (int) $this->payment->auto_complete,
        ];

        if ($this->config->isMarketplace) {
            $params = [...$params, 'is_pool_payment' => 1, 'commission_scenario' => 3, 'sub_dealers' => $this->dealer_amounts];
        }

        $this->middleware(PaymentPay3dMiddleware::class);

        $result = $this->http->post(payment: $this->payment, uri: $uri, params: $params)->toArray();

        return [
            'parapos_code' => Arr::get($result, 'data.id'),
            'url' => Arr::get($result, 'data.url'),
        ];
    }

    public function addCard(
        string $card_number,
        string $name,
        string $cvv2,
        string $expire_date_month,
        string $expire_date_year
    ): self {
        $this->card = new CardDataObject(
            card_number: $card_number,
            name: $name,
            cvv2: $cvv2,
            expire_date_month: $expire_date_month,
            expire_date_year: $expire_date_year,
        );

        return $this;
    }

    public function addDealerAmount(
        string $dealer_id,
        float $amount,
        float $dealer_commission_amount,
        float $dealer_commission_fixed_amount = 0
    ): self {

        $this->dealer_amounts[] = new DealerAmountDataObject(
            dealer_id: $dealer_id,
            amount: $amount,
            dealer_commission_amount: $dealer_commission_amount,
            dealer_commission_fixed_amount: $dealer_commission_fixed_amount,
        );

        return $this;
    }

    public function addPayment(
        string $client_ip,
        float $amount,
        int|string|null $payment_id = null,
        int|string|null $user_id = null,
        int|string|null $reference_id = null,
        string $currency_code = 'TRY',
        int $installment = 1,
        float $ratio = 0,
        int|string|null $foreign_id_1 = null,
        int|string|null $foreign_id_2 = null,
        int|string|null $foreign_id_3 = null,
        ?string $request_code = null,
        ?string $response_code = null,
        ?string $description = null,
        bool $is_pre_auth = false,
        bool $auto_complete = false
    ): self {
        $this->payment = (new FindOrNewPaymentAction($this->config->model))($payment_id);
        $this->payment->ip = $client_ip;
        $this->payment->amount = $amount;
        $this->payment->installment = $installment;
        $this->payment->ratio = $ratio;
        $this->payment->currency_code = $currency_code;
        $this->payment->user_id = $user_id;
        $this->payment->reference_id = $reference_id;
        $this->payment->foreign_id_1 = $foreign_id_1;
        $this->payment->foreign_id_2 = $foreign_id_2;
        $this->payment->foreign_id_3 = $foreign_id_3;
        $this->payment->request_code = $request_code;
        $this->payment->response_code = $response_code;
        $this->payment->description = $description;
        $this->payment->is_pre_auth = $is_pre_auth ? 1 : 0;
        $this->payment->auto_complete = $auto_complete ? 1 : 0;
        $this->payment->save();

        return $this;
    }
}
