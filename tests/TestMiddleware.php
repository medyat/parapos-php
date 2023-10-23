<?php

namespace Tests;

use MedyaT\Parapos\Config\HttpRequest;
use MedyaT\Parapos\Config\HttpResponse;

class TestMiddleware
{
    public function __invoke(HttpRequest $request, $next): HttpResponse
    {

        $request->params['test_request_1'] = 'test_request_1';

        /** @var HttpResponse $response */
        $response = $next($request);

        $response->response .= ':test_response_1';

        return $response;
    }
}
