<?php

namespace MedyaT\Parapos\Middlewares;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

final class VerifyResponseMiddleware implements VerifyResponseMiddlewareInterface
{
    /**
     * @return mixed
     */
    public function __invoke(Request $request, Model $payment) {}
}
