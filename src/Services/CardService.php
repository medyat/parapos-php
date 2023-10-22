<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use MedyaT\Parapos\Config\Service;

final class CardService extends Service
{
    public function bin(string $bin): string
    {
        return $this->http->post('bin', ['bin' => $bin]);
    }

    /**
     * @param  float[]  $subAmounts
     */
    public function installment(string $bin, float $amount, array $subAmounts = []): string
    {
        $params = [
            'bin' => $bin,
            'amount' => $amount,
        ];

        if ($subAmounts !== []) {
            $params['sub_amounts'] = $subAmounts;
        }

        return $this->http->post('installment', $params);
    }
}
