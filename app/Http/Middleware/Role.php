<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ($role === 'admin' && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access');
        }

        if ($role === 'user' && auth()->user()->hasRole('admin')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}