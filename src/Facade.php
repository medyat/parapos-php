<?php

namespace MedyaT\Parapos;

final class Facade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Parapos::class;
    }
}
