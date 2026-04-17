<?php

namespace App\Http\Middleware;

use App\Models\Team;
use Closure;
use Illuminate\Http\Request;

class SetTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Extract tenant ID from the route
        $tenantId = $request->route('tenant_id');

        // Find the tenant
        $tenant = Team::findOrFail(1);

        // Set the tenant context
        app()->make('tenant')->setTenant($tenant);

        return $next($request);
    }
}
