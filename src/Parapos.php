<?php

declare(strict_types=1);

namespace MedyaT\Parapos;

use MedyaT\Parapos\Config\Config;
use MedyaT\Parapos\Services\CardService;
use MedyaT\Parapos\Services\PaymentService;

final class Parapos
{
    public Config $config;

    /**
     * @param  string[]|Config  $config
     */
    public function __construct(array|Config $config = [])
    {
        $this->config = $config instanceof Config
            ? $config
            : new Config($config);
    }

    public function payment(): PaymentService
    {
        return new PaymentService($this->config);
    }

    public function card(): CardService
    {
        return new CardService($this->config);
    }
}
