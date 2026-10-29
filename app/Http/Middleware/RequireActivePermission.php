<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\ActiveRoles;
use App\Support\RoleAccess;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * Otorisasi per route menurut peran aktif: `izin.aktif:backoffice.access,backoffice.sk.register`
 * needs every listed permission from the active role (or given to the
 * account itself); `a|b` takes either, `peran:admin` asks for the active
 * role itself. Unlike `izin` (any role held), a role the officer holds but
 * has not made active does not count. A refusal names the active role and
 * the roles held that would do, so the client can switch (/api/peran-aktif).
 */
class RequireActivePermission
{
    public function handle(Request $request, Closure $next, string ...$requirements): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            abort(401, 'Silakan masuk terlebih dahulu.');
        }

        // Every argument must hold; within one, any alternative.
        $needs = collect($requirements)->map(fn (string $r) => collect(explode('|', $r))->map(fn (string $n) => trim($n))->filter()->values())->filter->isNotEmpty();
        $allowed = $user->is_active !== false
            && $needs->every(fn ($any) => $any->contains(fn (string $need) => self::holds($user, $need)));

        if ($allowed) {
            return $next($request);
        }

        $active = ActiveRoles::inContext($user);
        $label = $needs->map(fn ($any) => $any->map(fn (string $need) => str_starts_with($need, 'peran:')
            ? 'peran ' . RoleAccess::roleLabel(substr($need, 6))
            : RoleAccess::permissionLabel($need))->join(' atau '))->join(', serta ');
        $others = self::rolesThatWould($user, $needs, $active);
        $message = 'Peran aktif Anda (' . ($active ? RoleAccess::roleLabel($active) : 'tidak ada') . ") tidak berwenang untuk ini (perlu: {$label})."
            . ($others ? ' Ganti peran aktif ke ' . implode(' atau ', array_map(RoleAccess::roleLabel(...), $others)) . '.' : '');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'peran_aktif' => $active, 'peran_yang_berwenang' => $others], 403);
        }

        abort(403, $message);
    }

    private static function holds(User $user, string $need): bool
    {
        return str_starts_with($need, 'peran:')
            ? ActiveRoles::hasRole($user, substr($need, 6))
            : ActiveRoles::can($user, $need);
    }

    /**
     * Staff roles the user holds, other than the active one, whose permissions meet $needs.
     *
     * @return list<string>
     */
    private static function rolesThatWould(User $user, $needs, ?string $active): array
    {
        return ActiveRoles::for($user)->pluck('name')
            ->reject(fn (string $role) => $role === $active)
            ->filter(function (string $role) use ($needs) {
                $model = rescue(fn () => Role::findByName($role, 'web'), null, report: false);

                return $model && $needs->every(fn ($any) => $any->contains(fn (string $need) => str_starts_with($need, 'peran:')
                    ? substr($need, 6) === $role
                    : $model->hasPermissionTo($need, 'web')));
            })
            ->values()->all();
    }
}
