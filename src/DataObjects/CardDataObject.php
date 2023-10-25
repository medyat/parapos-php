<?php

namespace MedyaT\Parapos\DataObjects;

final class CardDataObject
{
    public function __construct(
        public string $card_number,
        public string $name,
        public string $cvv2,
        public string $expire_date_month,
        public string $expire_date_year,
    ) {

        $this->expire_date_year = substr($this->expire_date_year, -2);
    }
}
