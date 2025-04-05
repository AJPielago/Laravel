<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Comprehensive logging for admin middleware
        $currentUser = Auth::user();
        
        // Detailed logging with explicit checks
        $roleCheckDetails = [
            'role_value' => $currentUser->role,
            'role_lowercase' => strtolower($currentUser->role),
            'is_active' => $currentUser->is_active,
            'email_verified' => $currentUser->email_verified_at !== null,
            'role_type' => gettype($currentUser->role),
            'active_type' => gettype($currentUser->is_active)
        ];

        Log::channel('daily')->info('Admin Middleware Check', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route' => $request->route() ? $request->route()->getName() : 'No route name',
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'user_email' => $currentUser->email,
            'user_role' => $currentUser->role,
            'role_check_details' => $roleCheckDetails
        ]);

        // Check if user is authenticated
        if (!Auth::check()) {
            Log::channel('daily')->warning('Unauthenticated access attempt to admin route');
            return redirect()->route('login')->with('error', 'Please log in to access this page');
        }

        // Comprehensive admin role check
        $isAdmin = false;
        $adminCheckReasons = [];

        // Check 1: Exact role match (case-insensitive)
        if (strtolower($currentUser->role) === 'admin') {
            $adminCheckReasons[] = 'exact_role_match';
        }

        // Check 2: Active status
        if ($currentUser->is_active == true) {
            $adminCheckReasons[] = 'is_active';
        }

        // Check 3: Email verification
        if ($currentUser->email_verified_at !== null) {
            $adminCheckReasons[] = 'email_verified';
        }

        $isAdmin = count($adminCheckReasons) === 3;

        if (!$isAdmin) {
            Log::channel('daily')->warning('Non-admin user attempted to access admin route', [
                'user_id' => $currentUser->id,
                'user_role' => $currentUser->role,
                'admin_check_reasons' => $adminCheckReasons,
                'role_check_details' => $roleCheckDetails
            ]);

            return redirect()->route('welcome')->with('error', 
                'Admin access required. ' . 
                'Current role: ' . $currentUser->role . 
                ', Active status: ' . ($currentUser->is_active ? 'Yes' : 'No') .
                ', Verification status: ' . ($currentUser->email_verified_at ? 'Verified' : 'Not Verified') .
                ', Check reasons: ' . implode(', ', $adminCheckReasons)
            );
        }

        return $next($request);
    }
}