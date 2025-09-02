<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SetClientAuthToken
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
        $response = $next($request);
        
        // Only set token for authenticated admin users
        if (auth()->check() && $request->routeIs('admin.*')) {
            $user = auth()->user();
            
            // Check if user has permission to access admin
            if ($user->can('system.dashboard') || $user->isSuperAdministrator) {
                
                // Check if client auth token already exists and is valid
                $existingToken = $request->cookie('buskincity_auth_client');
                $tokenExpiry = $request->cookie('buskincity_auth_client_expiry');
                
                $needsNewToken = true;
                
                if ($existingToken && $tokenExpiry) {
                    // Check if existing token is still valid (not expired)
                    if (time() < (int)$tokenExpiry) {
                        $needsNewToken = false;
                    }
                }
                
                if ($needsNewToken) {
                    // Generate new auth token
                    $authToken = Str::random(64);
                    $expiry = time() + (120 * 60); // 2 hours
                    
                    // Set the client auth token as a non-HttpOnly cookie
                    $response->cookie(
                        'buskincity_auth_client',
                        $authToken,
                        120, // 2 hours in minutes
                        '/',
                        null,
                        false, // not secure (set to true for HTTPS)
                        false  // not HttpOnly (accessible to JavaScript)
                    );
                    
                    // Set expiry as separate cookie for JavaScript validation
                    $response->cookie(
                        'buskincity_auth_client_expiry',
                        $expiry,
                        120, // 2 hours in minutes
                        '/',
                        null,
                        false, // not secure (set to true for HTTPS)
                        false  // not HttpOnly (accessible to JavaScript)
                    );
                    
                    // Add token to shared Inertia data for JavaScript initialization
                    if ($request->header('X-Inertia')) {
                        \Inertia\Inertia::share('authToken', $authToken);
                        \Inertia\Inertia::share('authTokenExpiry', $expiry);
                    }
                }
            }
        }
        
        return $response;
    }
}