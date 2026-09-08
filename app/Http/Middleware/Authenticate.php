<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo($request): ?string
    {
        if (! $request->expectsJson()) {
            // Area Admin
            if (request()->is('panel/*')) {
                return route('loginadmin');
            }

            // Area Karyawan
            return route('login');
        }

        return null;
    }
}
