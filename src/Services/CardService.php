<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use MedyaT\Parapos\Config\Service;

final class CardService extends Service
{
    /**
     * @return mixed[]
     */
    public function bin(string $bin, int $payment_id = null): array
    {
        $params = ['bin' => $bin];

        if ($payment_id !== null) {
            $params['payment_id'] = $payment_id;
        }

        return $this->http->post('bin', $params)->toArray();
    }

    /**
     * @param  float[]  $subAmounts
     * @return mixed[]
     */
    public function installment(string $bin, float $amount, array $subAmounts = [], int $payment_id = null): array
    {
        $params = [
            'bin' => $bin,
            'amount' => $amount,
        ];

        if ($subAmounts !== []) {
            $params['sub_amounts'] = $subAmounts;
        }

        if ($payment_id !== null) {
            $params['payment_id'] = $payment_id;
        }

        return $this->http->post('installment', $params)->toArray();
    }
}
