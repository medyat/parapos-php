<?php

namespace MedyaT\Parapos\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

interface VerifyResponseMiddlewareInterface
{
    /**
     * @return mixed
     */
    public function __invoke(Request $request, Model $payment);
}
