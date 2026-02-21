<?php

namespace MecenePhrygien\LaravelAcl\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleOrPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$rolesOrPermissions): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect(config('acl.redirects.login', '/login'));
        }

        $user = $request->user();
        $user->loadMissing('roles.permissions');

        foreach ($rolesOrPermissions as $value) {
            if ($user->hasRole($value) || $user->hasPermission($value)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        abort(403, 'Accès refusé.');
    }
}