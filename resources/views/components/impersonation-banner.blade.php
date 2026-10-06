{{-- Penanda sesi ganti akun sementara: tampil di atas setiap halaman selama administrator memakai akun lain. --}}
@php($session = \App\Support\Impersonation::current())
@if ($session && auth()->check() && $session['target'])
    <div role="status" aria-live="polite" data-impersonation-banner
        style="position:sticky;top:0;z-index:50;display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:.5rem 1rem;padding:.5rem 1rem;background:#b45309;color:#fff;font-size:.875rem;line-height:1.35;box-shadow:0 1px 0 rgba(0,0,0,.15)">
        <span style="display:flex;align-items:center;gap:.5rem;min-width:0">
            <svg aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
            <span>
                Anda sedang memakai akun <strong>{{ $session['target']->name }}</strong>
                ({{ \App\Support\RoleAccess::userRoleLabel($session['target']) }}).
                Akun asli: {{ $session['admin_name'] }}@if ($session['started_at']), sejak {{ $session['started_at']->translatedFormat('H.i') }}@endif.
            </span>
        </span>

        <form method="POST" action="{{ route('ganti-akun.selesai') }}" style="margin:0">
            @csrf
            <button type="submit" data-impersonation-leave
                style="padding:.25rem .75rem;border-radius:.375rem;background:#fff;color:#92400e;font-weight:600;border:0;cursor:pointer">
                Kembali ke akun admin
            </button>
        </form>
    </div>
@endif
