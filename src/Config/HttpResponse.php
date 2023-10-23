<?php

namespace MedyaT\Parapos\Config;

final class HttpResponse implements \Stringable
{
    public function __construct(public string $response)
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
        return (array) json_decode($this->response, true, 512, JSON_THROW_ON_ERROR);
    }
}
