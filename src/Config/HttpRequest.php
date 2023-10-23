<?php

namespace MedyaT\Parapos\Config;

use MedyaT\Parapos\Models\Payment;

final class HttpRequest implements \Stringable
{
    public Payment $payment;

    /**
     * @param  string[]  $headers
     * @param  mixed[]  $params
     */
    public function __construct(public string $uri, public string $method, public array $headers, public array $params = [])
    {

    }

    public function __toString(): string
    {
        return json_encode([
            'uri' => $this->uri,
            'method' => $this->method,
            'headers' => $this->headers,
            'params' => $this->params,
        ], JSON_THROW_ON_ERROR);
    }
}
