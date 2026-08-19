<?php

use BlackpigCreatif\Confessionnal\Livewire\FormFill;
use Illuminate\Support\Facades\Route;

$prefix = config('confessionnal.route_prefix', 'forms');
$middleware = config('confessionnal.middleware', ['web']);

Route::middleware($middleware)
    ->prefix($prefix)
    ->group(function () {
        Route::get('{slug}/{locale?}', FormFill::class)
            ->name('confessionnal.fill');
    });
