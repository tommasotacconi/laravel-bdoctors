<?php

namespace App\Http\Middleware;

use App\Helpers\TimeHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TimeParser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('firstReqTime')) $request->attributes->set(
            'app_time',
            TimeHelper::normalizeToAppYear($request->query('firstReqTime'))
        );
        
        return $next($request);
    }
}
