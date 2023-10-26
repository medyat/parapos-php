<?php

namespace MedyaT\Parapos\Config;

use MedyaT\Parapos\Models\Payment;

final class HttpResponse implements \Stringable
{
    /** @param  mixed[]  $headers  */
    public function __construct(public Payment $payment, public string $response, public array $headers = [])
    {
    }

    public function __toString(): string
    {
        return $this->response;
    }

    /**
     * @return mixed[]
     *
     * @throws \JsonException
     */
    public function toArray()
    {
        $array = (array) json_decode($this->response, true, 512, JSON_THROW_ON_ERROR);

        if (isset($array['data']) && is_array($array['data']) && isset($this->payment)) {
            $array['data']['payment_id'] = $this->payment->id;
        }

        return $array;
    }
}
