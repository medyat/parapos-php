<?php

namespace MedyaT\Parapos\Middlewares;

use Illuminate\Http\Request;
use MedyaT\Parapos\Models\Payment;

interface VerifyResponseMiddlewareInterface
{
    /**
     * @return mixed
     */
    public function __invoke(Request $request, Payment $payment);
}
