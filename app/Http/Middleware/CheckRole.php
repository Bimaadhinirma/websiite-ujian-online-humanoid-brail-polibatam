<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        if ($role === 'admin' && $user->role !== 1) {
            abort(403, 'Unauthorized action.');
        }

        if ($role === 'participant' && $user->role !== 2) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
