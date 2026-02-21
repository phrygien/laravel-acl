<?php

namespace YourName\LaravelAcl\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }
            return redirect(config('acl.redirects.login', '/login'));
        }

        foreach ($permissions as $permission) {
            if (! $request->user()->hasPermission($permission)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "Permission manquante : {$permission}"
                    ], 403);
                }
                abort(403, "Permission manquante : {$permission}");
            }
        }

        return $next($request);
    }
}