<?php

// config for BlackpigCreatif/Confessionnal
return [

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The URI prefix for public form fill routes.
    | e.g. 'forms' => /forms/{slug}/{locale?}
    |
    */
    'route_prefix' => 'forms',

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to the public fill route.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Mappable Models
    |--------------------------------------------------------------------------
    |
    | Models that implement CanReceiveSubmissions and should be available
    | for target-model mapping. Auto-discovery scans app/Models/ for
    | implementations; list additional models here if they live elsewhere.
    |
    */
    'mappable_models' => [],

];
