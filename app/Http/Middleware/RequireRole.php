<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $u = $request->session()->get('user');
        if (!$u || !in_array($u['role'] ?? 'user', $roles, true)) {
            abort(403, 'Forbidden');
        }
        return $next($request);
    }
}
