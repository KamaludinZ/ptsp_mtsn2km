<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FrontDeskService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Pengguna (admin): list, read, create, change, deactivate and delete
 * accounts. An admin cannot lock themselves out, and the last active
 * administrator always stays.
 */
class UserController extends Controller
{
    /** GET /api/pengguna?q=&peran=&kategori=&aktif=&jenis=petugas|pemohon&per_halaman= */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'peran' => ['nullable', 'string', 'exists:roles,name'],
            'kategori' => ['nullable', Rule::in(array_keys(FrontDeskService::APPLICANT_TYPES))],
            'aktif' => ['nullable', 'boolean'],
            'jenis' => ['nullable', 'in:petugas,pemohon'],
            'per_halaman' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $staff = fn (Builder $q) => $q->whereIn('name', User::STAFF_ROLES);

        $page = User::query()->with('roles:id,name')
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->where(fn (Builder $q) => $q
                ->whereRaw('lower(name) like ?', ['%' . mb_strtolower($term) . '%'])
                ->orWhere('email', 'like', '%' . mb_strtolower($term) . '%')
                ->orWhere('whatsapp_number', 'like', '%' . preg_replace('/\D/', '', $term) . '%')))
            ->when($filters['peran'] ?? null, fn (Builder $q, string $role) => $q->role($role))
            ->when($filters['kategori'] ?? null, fn (Builder $q, string $type) => $q->where('user_type', $type))
            ->when(isset($filters['aktif']), fn (Builder $q) => $q->where('is_active', (bool) $filters['aktif']))
            ->when(($filters['jenis'] ?? null) === 'petugas', fn (Builder $q) => $q->whereHas('roles', $staff))
            ->when(($filters['jenis'] ?? null) === 'pemohon', fn (Builder $q) => $q->whereDoesntHave('roles', $staff))
            ->orderBy('name')
            ->paginate($filters['per_halaman'] ?? 20)
            ->withQueryString();

        return response()->json([
            'data' => collect($page->items())->map(fn (User $user) => AuthController::profile($user) + ['aktif' => (bool) $user->is_active]),
            'meta' => ['halaman' => $page->currentPage(), 'per_halaman' => $page->perPage(), 'total' => $page->total(), 'halaman_terakhir' => $page->lastPage()],
        ]);
    }

    /** GET /api/pengguna/{user} */
    public function show(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        return response()->json(self::item($user));
    }

    /** POST /api/pengguna {nama, email, password, kategori, whatsapp?, peran?[], aktif?} */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validated($request, null);

        $user = DB::transaction(function () use ($data) {
            $user = new User(['is_active' => true]);
            $user->is_active = (bool) ($data['aktif'] ?? true);
            $this->fill($user, $data);
            $user->email_verified_at = now(); // made by an admin: trusted
            $user->save();
            $user->syncRoles($data['peran'] ?? []);

            return $user;
        });
        activity('audit')->causedBy($request->user())->performedOn($user)->log('Menambah pengguna ' . $user->email);

        return response()->json(self::item($user), 201);
    }

    /** PATCH /api/pengguna/{user} (only the fields sent) */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);
        $data = $this->validated($request, $user);

        if ($user->is($request->user()) && array_key_exists('peran', $data) && ! in_array('admin', $data['peran'], true)) {
            return response()->json(['message' => 'Anda tidak dapat menghapus peran Administrator dari akun sendiri.'], 422);
        }
        if (array_key_exists('peran', $data) && $user->hasRole('admin') && ! in_array('admin', $data['peran'], true) && self::isLastAdmin($user)) {
            return response()->json(['message' => 'Harus ada setidaknya satu Administrator aktif.'], 422);
        }

        DB::transaction(function () use ($user, $data) {
            $this->fill($user, $data);
            $user->save();
            if (array_key_exists('peran', $data)) {
                $user->syncRoles($data['peran']);
            }
        });
        activity('audit')->causedBy($request->user())->performedOn($user)->log('Mengubah pengguna ' . $user->email);

        return response()->json(self::item($user->fresh()));
    }

    /** PATCH /api/pengguna/{user}/aktif {aktif}: deactivating also signs the account out of the API. */
    public function setActive(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);
        $active = (bool) $request->validate(['aktif' => ['required', 'boolean']])['aktif'];

        if (! $active && $user->is($request->user())) {
            return response()->json(['message' => 'Anda tidak dapat menonaktifkan akun sendiri.'], 422);
        }
        if (! $active && $user->hasRole('admin') && self::isLastAdmin($user)) {
            return response()->json(['message' => 'Harus ada setidaknya satu Administrator aktif.'], 422);
        }

        $user->forceFill(['is_active' => $active])->save();
        $revoked = $active ? 0 : $user->tokens()->delete();
        activity('audit')->causedBy($request->user())->performedOn($user)->log(($active ? 'Mengaktifkan' : 'Menonaktifkan') . ' pengguna ' . $user->email);

        return response()->json([
            'message' => $active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan dan dikeluarkan dari semua perangkat.',
            'aktif' => $active,
            'token_dicabut' => $revoked,
        ]);
    }

    /** POST /api/pengguna/{user}/kirim-reset-kata-sandi: e-mails the user a reset link (the admin never sees a password). */
    public function sendPasswordReset(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(['email' => $user->email]);
        activity('audit')->causedBy($request->user())->performedOn($user)->log('Mengirim tautan atur ulang kata sandi ke ' . $user->email);

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? response()->json(['message' => "Tautan atur ulang kata sandi dikirim ke {$user->email}."])
            : response()->json(['message' => __($status)], 429);
    }

    /** DELETE /api/pengguna/{user} (soft delete; history stays) */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request);
        if ($user->is($request->user())) {
            return response()->json(['message' => 'Anda tidak dapat menghapus akun sendiri.'], 422);
        }
        if ($user->hasRole('admin') && self::isLastAdmin($user)) {
            return response()->json(['message' => 'Harus ada setidaknya satu Administrator aktif.'], 422);
        }

        $user->tokens()->delete();
        $user->delete();
        activity('audit')->causedBy($request->user())->log('Menghapus pengguna ' . $user->email);

        return response()->json(['message' => 'Pengguna dihapus.']);
    }

    private function validated(Request $request, ?User $user): array
    {
        $required = $user ? 'sometimes' : 'required';
        $request->merge(array_filter(['email' => $request->has('email') ? mb_strtolower(trim((string) $request->input('email'))) : null]));

        return $request->validate([
            'nama' => [$required, 'string', 'max:255'],
            'email' => [$required, 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'password' => [$user ? 'sometimes' : 'required', 'string', Password::min(8)->letters()->numbers()],
            'kategori' => [$required, Rule::in(array_keys(FrontDeskService::APPLICANT_TYPES))],
            'whatsapp' => ['sometimes', 'nullable', 'regex:/^(\+?62|0)\s?8[0-9][0-9\s-]{6,14}$/'],
            'peran' => ['sometimes', 'array'],
            'peran.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'aktif' => ['sometimes', 'boolean'],
        ], [
            'email.unique' => 'Email sudah dipakai akun lain.',
            'whatsapp.regex' => 'Gunakan nomor HP Indonesia, mis. 081234567890.',
        ]);
    }

    private function fill(User $user, array $data): void
    {
        foreach (['nama' => 'name', 'email' => 'email', 'kategori' => 'user_type'] as $key => $column) {
            if (array_key_exists($key, $data)) {
                $user->{$column} = $data[$key];
            }
        }
        if (array_key_exists('whatsapp', $data)) {
            $user->whatsapp_number = filled($data['whatsapp']) ? preg_replace('/[^0-9+]/', '', $data['whatsapp']) : null;
        }
        if (filled($data['password'] ?? null)) {
            $user->password = Hash::make($data['password']);
        }
        if (array_key_exists('aktif', $data) && ! $user->is(auth()->user())) {
            $user->is_active = (bool) $data['aktif'];
        }
    }

    private static function isLastAdmin(User $user): bool
    {
        return ! User::role('admin')->where('is_active', true)->whereKeyNot($user->id)->exists();
    }

    private static function item(User $user): array
    {
        return AuthController::profile($user) + [
            'aktif' => (bool) $user->is_active,
            'dibuat' => $user->created_at?->toIso8601String(),
            'token_aktif' => $user->tokens()->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
        ];
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->hasRole('admin'), 403);
    }
}
