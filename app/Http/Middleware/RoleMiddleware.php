<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        if (!$user->isActive()) {
            Auth::logout();
            return redirect('/login')->with('error', 'Your account is not active.');
        }

        // Check if user has the required role (SRS: admin, cdc, hod, faculty, observer)
        switch ($role) {
            case 'admin':
                if (!$user->isAdmin()) {
                    abort(403, 'Access denied. Admin role required.');
                }
                break;
            case 'cdc':
                if (!$user->isCdc() && !$user->isAdmin()) {
                    abort(403, 'Access denied. CDC Incharge role required.');
                }
                break;
            case 'hod':
                if (!$user->isHod() && !$user->isAdmin()) {
                    abort(403, 'Access denied. HOD role required.');
                }
                break;
            case 'faculty':
            case 'creator':
                if (!$user->isFaculty() && !$user->isAdmin()) {
                    abort(403, 'Access denied. Faculty role required.');
                }
                break;
            case 'approver':
                if (!$user->isHod() && !$user->isAdmin()) {
                    abort(403, 'Access denied. HOD/Approver role required.');
                }
                break;
            case 'observer':
                if (!$user->isObserver() && !$user->isAdmin()) {
                    abort(403, 'Access denied. Observer role required.');
                }
                break;
            default:
                abort(403, 'Invalid role specified.');
        }

        return $next($request);
    }
}
