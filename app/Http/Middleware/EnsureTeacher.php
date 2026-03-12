<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTeacher
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || (! method_exists($user, 'isTeacher') || ! $user->isTeacher())) {
            abort(403, 'Access denied.');
        }
        return $next($request);
    }
}
