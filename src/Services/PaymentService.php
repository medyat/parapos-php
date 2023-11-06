<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use Illuminate\Support\Arr;
use MedyaT\Parapos\Actions\FindOrNewPaymentAction;
use MedyaT\Parapos\Config\Service;
use MedyaT\Parapos\DataObjects\CardDataObject;
use MedyaT\Parapos\DataObjects\DealerAmountDataObject;
use MedyaT\Parapos\Exceptions\NoCreditCardDefined;
use MedyaT\Parapos\Exceptions\NoPaymentDefined;
use MedyaT\Parapos\Middlewares\PaymentPay3dMiddleware;
use MedyaT\Parapos\Models\Payment;

final class PaymentService extends Service
{
    public Payment $payment;

    public CardDataObject $card;

    /** @var DealerAmountDataObject[] */
    public array $dealer_amounts = [];

    /**
     * @return array{parapos_code: mixed, url: mixed}
     */
    public function pay3d(): array
    {
        if (! isset($this->card)) {
            throw new NoCreditCardDefined();
        }

        if (! isset($this->payment)) {
            throw new NoPaymentDefined();
        }

        $params = [
            'card_number' => $this->card->card_number,
            'name' => $this->card->name,
            'cvv2' => $this->card->cvv2,
            'expire_date_month' => $this->card->expire_date_month,
            'expire_date_year' => $this->card->expire_date_year,

            'installment' => $this->payment->installment,
            'amount' => $this->payment->amount,
            'client_ip' => $this->payment->ip,
            'client_order_id' => $this->payment->response_hash,
            'success_url' => $this->config->getResponseUrl($this->payment->response_hash),
            'fail_url' => $this->config->getResponseUrl($this->payment->response_hash),
        ];

        if ($this->config->isMarketplace) {
            $params = [...$params, 'is_pool_payment' => 1, 'commission_scenario' => 3, 'sub_dealers' => $this->dealer_amounts];
        }

        $this->middleware(PaymentPay3dMiddleware::class);

        $result = $this->http->post(payment: $this->payment, uri: 'pay_3d', params: $params)->toArray();

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
        int $payment_id = null,
        int $user_id = null,
        int $reference_id = null,
        string $currency_code = 'TRY',
        int $installment = 1,
        int $foreign_id_1 = null,
        int $foreign_id_2 = null,
        int $foreign_id_3 = null,
    ): self {
        $this->payment = (new FindOrNewPaymentAction())($payment_id);
        $this->payment->ip = $client_ip;
        $this->payment->amount = $amount;
        $this->payment->installment = $installment;
        $this->payment->currency_code = $currency_code;
        $this->payment->user_id = $user_id;
        $this->payment->reference_id = $reference_id;
        $this->payment->foreign_id_1 = $foreign_id_1;
        $this->payment->foreign_id_2 = $foreign_id_2;
        $this->payment->foreign_id_3 = $foreign_id_3;
        $this->payment->save();

        return $this;
    }
}
