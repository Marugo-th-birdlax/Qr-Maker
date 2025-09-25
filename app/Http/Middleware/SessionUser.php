<?php

// app/Http/Middleware/SessionUser.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionUser {
    public function handle(Request $request, Closure $next) {
        if (Auth::check()) {
            $u = Auth::user();
            session([
              'user' => [
                'id'          => $u->id,
                'email'       => $u->email,
                'first_name'  => $u->first_name ?: ($u->name ?: ''),
                'last_name'   => $u->last_name ?: '',
                'employee_id' => $u->employee_id,
                'role'        => $u->role ?? 'user',
              ]
            ]);
        } else {
            session()->forget('user');
        }
        return $next($request);
    }
}
