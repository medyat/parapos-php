<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use MedyaT\Parapos\Config\Service;
use MedyaT\Parapos\Models\Payment;

final class PosService extends Service
{
    /**
     * @return mixed[]
     */
    public function getRatios(): array
    {
        return $this->http->get(payment: new Payment(), uri: 'pos/active')->toArray();
    }
}
