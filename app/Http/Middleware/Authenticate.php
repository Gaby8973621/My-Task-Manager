<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // Evita redirección a 'login' en APIs
        if (! $request->expectsJson()) {
            return null;
        }
    }
}
