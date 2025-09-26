<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;


class Kernel extends HttpKernel
{
    protected $routeMiddleware = [
        // ...
        
        'auth.session' => \App\Http\Middleware\RequireLogin::class,
        'role'         => \App\Http\Middleware\RequireRole::class,
        'require.role' => \App\Http\Middleware\RequireRole::class, 
    ];

}