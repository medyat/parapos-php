<?php

use Illuminate\Support\Facades\Route;
use MedyaT\Parapos\Http\HandleResponseAction;

Route::post('parapos/response/{hash}/{tenant?}', HandleResponseAction::class)
    ->middleware('parapos-middleware')
    ->name('parapos.response');
