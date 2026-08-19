<?php

namespace BlackpigCreatif\Confessionnal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BlackpigCreatif\Confessionnal\Confessionnal
 */
class Confessionnal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \BlackpigCreatif\Confessionnal\Confessionnal::class;
    }
}
