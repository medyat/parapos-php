<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Services;

use MedyaT\Parapos\Config\Service;

final class PaymentService extends Service
{
    public function pay(): string
    {
        return 'bin';
    }

    public function pay_3d(): string
    {
        return 'bin';
    }
}
