<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $accesses): Response
    {
        $user = auth()->user();

        if (! $user) {
            abort(401);
        }

        // Admin always allowed
        if ($user->role === 'admin') {
            return $next($request);
        }

        // support multiple accesses: vault,reports
        $accessList = explode(',', $accesses);

        foreach ($accessList as $access) {
            if ($user->hasAccess(trim($access))) {
                return $next($request);
            }
        }

        abort(403, 'Access denied.');
    }
}
