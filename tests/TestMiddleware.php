<?php

namespace Tests;

class TestMiddleware
{
    public function __invoke($request, $next)
    {

        $request['params']['test_request_1'] = 'test_request_1';

        $response = $next($request);

        $response .= ':test_response_1';

        return $response;
    }
}
