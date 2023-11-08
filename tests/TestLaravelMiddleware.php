<?php

namespace Tests;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TestLaravelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // dd('middleware', $request->all(), $request->route('hash'), $request->route('tenant'));

        abort_if($request->route('hash') == 'fail', 444, 'fail');

        abort_if($request->route('tenant') == 'tenant-fail', 445, 'tenant-fail');

        return $next($request);
    }
}
