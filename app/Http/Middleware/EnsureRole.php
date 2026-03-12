<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $hasRole = match($role) {
            'admin' => $user->isAdmin(),
            'cdc' => $user->isCdc() || $user->isAdmin(),
            'hod' => $user->isHod() || $user->isAdmin(),
            'faculty' => $user->isFaculty() || $user->isAdmin(),
            'creator' => $user->isFaculty() || $user->isAdmin(),
            'approver' => $user->isHod() || $user->isAdmin(),
            'observer' => $user->isObserver() || $user->isAdmin(),
            default => false,
        };

        if (!$hasRole) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
