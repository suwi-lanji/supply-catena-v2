<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class SetTenantContext
{
    public function handle($request, Closure $next)
    {
        // Assuming you have a way to get the tenant ID from the request or Auth
        if (Auth::check()) {
            // Set the tenant context based on the authenticated user
            app()->make('tenant')->setTenant(1);
        }

        return $next($request);
    }
}
