<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Permissions;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) return redirect()->guest(route('login'));
        if (!$request->user()->is_active || !isset(Permissions::ROLES[$request->user()->role])) {
            $message = $request->user()->is_active ? 'Your account does not have an assigned role. Contact a superadmin.' : 'Your account has been deactivated.';
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => $message]);
        }
        // Load fresh permissions on each request, including after an administrator revokes access.
        $request->user()->unsetRelation('roleDefinition');
        abort_unless($request->user()->canAccessAdminRoute($request->route()->getName()), 403, 'You do not have permission to access this area.');
        return $next($request);
    }
}
