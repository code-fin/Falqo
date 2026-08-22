<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->company_id) {
            setPermissionsTeamId($request->user()->company_id);
        }

        return $next($request);
    }
}
