<?php

namespace MedyaT\Parapos\DataObjects;

final class DealerAmountDataObject
{
    public function __construct(
        public string $dealer_id,
        public float $amount,
        public float $dealer_commission_amount,
        public float $dealer_commission_fixed_amount = 0
    ) {

        $this->amount = round($amount, 2);
        $this->dealer_commission_amount = round($dealer_commission_amount, 2);
    }
}
