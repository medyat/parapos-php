<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use MedyaT\Parapos\Config\Service;

final class CardService extends Service
{
    /**
     * @return mixed[]
     */
    public function bin(string $bin): array
    {
        return $this->http->post('bin', ['bin' => $bin])->toArray();
    }

    /**
     * @param  float[]  $subAmounts
     * @return mixed[]
     */
    public function installment(string $bin, float $amount, array $subAmounts = []): array
    {
        $params = [
            'bin' => $bin,
            'amount' => $amount,
        ];

        if ($subAmounts !== []) {
            $params['sub_amounts'] = $subAmounts;
        }

        return $this->http->post('installment', $params)->toArray();
    }
}
