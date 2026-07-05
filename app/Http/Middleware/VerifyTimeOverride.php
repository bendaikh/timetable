<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTimeOverride
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local', 'testing')) {
            $verifyTime = $request->header('X-Verify-Time');
            if (is_string($verifyTime) && trim($verifyTime) !== '') {
                Carbon::setTestNow(Carbon::parse($verifyTime));
            } else {
                Carbon::setTestNow();
            }
        }

        return $next($request);
    }
}
