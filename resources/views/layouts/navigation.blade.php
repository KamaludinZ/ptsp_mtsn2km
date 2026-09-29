@php
    $navItems = [
        ['label' => 'Beranda', 'icon' => 'fa-house', 'href' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Layanan', 'icon' => 'fa-concierge-bell', 'href' => route('onlineportal.service.catalog'), 'active' => request()->routeIs('onlineportal.service.*')],
        ['label' => 'Tentang', 'icon' => 'fa-circle-info', 'href' => route('public.about'), 'active' => request()->routeIs('public.about')],
        ['label' => 'Buku Tamu', 'icon' => 'fa-book-open', 'href' => route('public.visitor.book'), 'active' => request()->routeIs('public.visitor.*')],
        ['label' => 'Survei', 'icon' => 'fa-square-poll-vertical', 'href' => route('survey.form'), 'active' => request()->routeIs('survey.*', 'supervision.skm.*')],
        ['label' => 'Pengaduan', 'icon' => 'fa-comments', 'href' => route('supervision.complaints.dashboard'), 'active' => request()->routeIs('supervision.complaint*', 'supervision.whistleblowing.*')],
        ['label' => 'Lacak Tiket', 'icon' => 'fa-magnifying-glass', 'href' => route('onlineportal.track.ticket.form'), 'active' => request()->routeIs('onlineportal.track.*')],
        ['label' => 'Pengumuman', 'icon' => 'fa-bullhorn', 'href' => route('pengumuman.index'), 'active' => request()->routeIs('pengumuman.*')],
    ];
    $brandName = app_brand_name();
    $logo = config('app.logo') ? asset(config('app.logo')) : asset('images/kemenag-logo.png');
@endphp

<header class="site-header" role="banner">
    <div class="site-header__inner">
        <!-- Menu toggle (below xl) -->
        <button type="button" class="site-icon-btn site-menu-toggle" data-panel-toggle="site-mobile-menu"
                aria-expanded="false" aria-controls="site-mobile-menu" aria-label="Buka menu navigasi">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <a href="{{ route('home') }}" class="site-brand" title="{{ $brandName }}">
            <img src="{{ $logo }}" alt="" width="36" height="36">
            <span>{{ $brandName }}</span>
        </a>

        <nav class="site-nav" aria-label="Navigasi utama">
            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}" @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="site-actions">
            <button type="button" class="site-icon-btn" id="site-theme-toggle" aria-label="Ganti tema terang/gelap">
                <i class="fas fa-moon theme-icon-light" aria-hidden="true"></i>
                <i class="fas fa-sun theme-icon-dark" aria-hidden="true"></i>
            </button>

            @auth
                @php
                    $authUser = Auth::user();
                    $dashboardLink = get_dashboard_route_for_user($authUser);
                @endphp
                <div class="site-user">
                    <button type="button" class="site-user-btn" data-panel-toggle="site-user-menu"
                            aria-expanded="false" aria-controls="site-user-menu" aria-label="Menu akun {{ $authUser->name }}">
                        <span class="site-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($authUser->name, 0, 1)) }}</span>
                        <span class="site-user-name">{{ \Illuminate\Support\Str::limit($authUser->name, 18) }}</span>
                        <i class="fas fa-chevron-down site-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="site-panel site-user-panel" id="site-user-menu" hidden>
                        <p class="site-panel-title">{{ $authUser->name }}</p>
                        <a href="{{ $dashboardLink }}"><i class="fas fa-gauge-high" aria-hidden="true"></i>Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="site-panel-danger"><i class="fas fa-right-from-bracket" aria-hidden="true"></i>Keluar</button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="site-btn site-btn-solid">
                    <i class="fas fa-right-to-bracket" aria-hidden="true"></i><span>Masuk</span>
                </a>
                <a href="{{ route('register') }}" class="site-btn site-btn-outline site-btn-register">
                    <i class="fas fa-user-plus" aria-hidden="true"></i><span>Daftar</span>
                </a>
            @endauth
        </div>
    </div>

    <!-- Mobile / tablet menu -->
    <div class="site-panel site-mobile-panel" id="site-mobile-menu" hidden>
        <nav aria-label="Navigasi seluler">
            @foreach($navItems as $item)
                <a href="{{ $item['href'] }}" @if($item['active']) aria-current="page" @endif>
                    <i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        @guest
            <div class="site-mobile-cta">
                <a href="{{ route('login') }}" class="site-btn site-btn-solid"><i class="fas fa-right-to-bracket" aria-hidden="true"></i>Masuk</a>
                <a href="{{ route('register') }}" class="site-btn site-btn-outline"><i class="fas fa-user-plus" aria-hidden="true"></i>Daftar</a>
            </div>
        @endguest
    </div>
</header>

<script>
(function () {
    var root = document.documentElement;

    // Theme: one owner for the header toggle (shares the "theme" key with app.js)
    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        root.classList.toggle('dark', theme === 'dark');
        // Public pages also switch Bootstrap's own colour mode (cards, forms, tables)
        if (root.classList.contains('public-site')) root.setAttribute('data-bs-theme', theme);
    }
    try {
        var saved = localStorage.getItem('theme');
        if (saved === 'dark' || saved === 'light') applyTheme(saved);
    } catch (e) {}

    var toggle = document.getElementById('site-theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        });
    }

    // Disclosure panels (mobile menu, account menu)
    var toggles = document.querySelectorAll('[data-panel-toggle]');
    function setOpen(btn, open) {
        var panel = document.getElementById(btn.getAttribute('data-panel-toggle'));
        if (!panel) return;
        panel.hidden = !open;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    function closeAll(except) {
        toggles.forEach(function (btn) { if (btn !== except) setOpen(btn, false); });
    }
    toggles.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var open = btn.getAttribute('aria-expanded') !== 'true';
            closeAll(btn);
            setOpen(btn, open);
        });
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.site-panel')) closeAll(null);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll(null);
    });
})();
</script>
