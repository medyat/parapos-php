<?php

namespace Tests;

use MedyaT\Parapos\Http\HandleResponseAction;

/**
 * How a host app registers a second callback route against its own model and
 * middleware stack, without touching global config.
 */
class TestCustomResponseController extends HandleResponseAction
{
    protected function model(): string
    {
        return TestCustomPayment::class;
    }

    /**
     * @return array<int, class-string>
     */
    protected function responseMiddlewares(): array
    {
        return [];
    }
}
