<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoCacheDashboard
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Prevent browser and proxy caching for dashboard
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->header('Cache-Control', 'post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        
        return $response;
    }
}
