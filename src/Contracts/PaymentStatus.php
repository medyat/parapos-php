<?php

declare(strict_types=1);

namespace MedyaT\Parapos\Contracts;

interface PaymentStatus
{
    public const PAYMENT_SUCCESS = 0;

    public const PAYMENT_PENDING = 1;

    public const PAYMENT_FAIL = 2;

    public const PAYMENT_PRE_AUTHORIZED = 3;

    public const PAYMENT_PRE_AUTH_CANCELLED = 4;

    public const PAYMENT_PRE_AUTH_CANCEL_FAILED = 5;

    public const PAYMENT_PRE_AUTH_CANCEL_ABANDONED = 6;

    public const PAYMENT_COMPLETE_PENDING = 7;
}
