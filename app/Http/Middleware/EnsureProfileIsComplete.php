<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->user()->hasCompleteProfile()) {
            return to_route('profile.complete');
        }

        return $next($request);
    }
}
