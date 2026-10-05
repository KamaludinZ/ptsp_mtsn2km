<?php

namespace App\Http\Middleware;

use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Otorisasi izin per route: `izin:backoffice.access|supervision.access`
 * lets the request through when the user has ANY of the permissions (or the
 * role, written as `peran:admin`). Unlike Spatie's middleware it works for
 * every guard (the roles live on "web"; API requests come in on "sanctum").
 */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string ...$requirements): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Silakan masuk terlebih dahulu.');
        }

        $needs = collect($requirements)->flatMap(fn (string $r) => explode('|', $r))->map(fn (string $r) => trim($r))->filter();
        $allowed = $user->is_active !== false && $needs->contains(fn (string $need) => str_starts_with($need, 'peran:')
            ? $user->hasRole(substr($need, 6), 'web')
            : $user->checkPermissionTo($need, 'web')); // false (not an error) for unknown permissions

        if (! $allowed) {
            $labels = $needs->map(fn (string $need) => str_starts_with($need, 'peran:')
                ? 'peran ' . RoleAccess::roleLabel(substr($need, 6))
                : RoleAccess::permissionLabel($need))->join(' atau ');

            abort(403, "Anda tidak memiliki izin untuk ini (perlu: {$labels}).");
        }

        return $next($request);
    }
}
