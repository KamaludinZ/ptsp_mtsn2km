# Production Deployment Guide
## PTSP MTsN 2 Kota Malang

### Vite untuk Development, Static Assets untuk Production

---

## 📋 Daftar Isi
1. [Konsep Deployment](#konsep-deployment)
2. [Development Workflow](#development-workflow)
3. [Production Build](#production-build)
4. [Deployment ke Server](#deployment-ke-server)
5. [Cara Menggunakan Assets](#cara-menggunakan-assets)
6. [Troubleshooting](#troubleshooting)

---

## Konsep Deployment

### Development (Lokal):
```
✅ Vite Dev Server Running (npm run dev)
✅ Hot Module Replacement (HMR)
✅ Fast refresh on file changes
✅ Source maps untuk debugging
```

### Production (Server):
```
✅ NO Vite Server
✅ NO npm run dev
✅ NO Node.js required on server
✅ HANYA compiled static assets
✅ Assets di-serve langsung dari public/build/
```

### Flow:

```
Development (Local)                Production (Server)
├── npm run dev                   ├── (NO npm, NO Vite)
├── Vite HMR                      ├── Static files only
├── @vite directive               ├── Helper functions
└── localhost:5173                └── public/build/assets/
```

---

## Development Workflow

### 1. Setup Development Environment

```bash
# Install dependencies
npm install
composer install

# Copy environment file
copy .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --seed
```

### 2. Start Development Servers

**Opsi A: Menggunakan Script (Recommended)**
```bash
dev-server.bat
```

**Opsi B: Manual (2 terminals)**
```bash
# Terminal 1: Vite
npm run dev

# Terminal 2: Laravel
php artisan serve
```

### 3. Development dengan @vite Directive

Gunakan `@vite` di layout files untuk development:

```blade
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

---

## Production Build

### Langkah 1: Build Assets

```bash
# Jalankan production build
build-production.bat
```

Script ini akan:
1. ✅ Install npm dependencies
2. ✅ Compile assets (`npm run build`)
3. ✅ Optimize Composer autoload
4. ✅ Clear Laravel caches
5. ✅ Optimize Laravel (config/route/view cache)
6. ✅ Verify build

### Langkah 2: Verify Build

```bash
# Check deployment readiness
deploy-check.bat
```

### Langkah 3: Hasil Build

Setelah `npm run build`, assets akan ada di:
```
public/build/
├── manifest.json              ← Asset mapping file
└── assets/
    ├── app-[hash].css        ← Compiled Tailwind CSS
    ├── app-[hash].js         ← Compiled JavaScript
    ├── bootstrap-custom-[hash].css
    └── bootstrap-bundle-[hash].js
```

**PENTING:** Hash berubah setiap build untuk cache busting.

---

## Deployment ke Server

### Method 1: Traditional Upload (Cpanel/FTP)

#### Step 1: Build di Local

```bash
# Build production assets
npm run build

# Optimize autoload
composer dump-autoload --optimize
```

#### Step 2: Upload Files

Upload ke server via FTP/Cpanel:
```
✅ app/
✅ bootstrap/
✅ config/
✅ database/
✅ public/           ← INCLUDING public/build/
✅ resources/
✅ routes/
✅ storage/
✅ vendor/           ← Hasil composer install
✅ .env              ← Configure untuk production
✅ artisan
✅ composer.json
✅ composer.lock
```

**TIDAK PERLU upload:**
```
❌ node_modules/    ← Tidak diperlukan di server
❌ .git/
❌ tests/
❌ .env.example
```

#### Step 3: Set Permissions (di server)

```bash
# Via SSH
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Via Cpanel File Manager
# Right click → Change Permissions
# storage: 755
# bootstrap/cache: 755
```

#### Step 4: Configure .env

```env
APP_NAME="PTSP MTsN 2 Kota Malang"
APP_ENV=production
APP_KEY=base64:... (generate dengan: php artisan key:generate)
APP_DEBUG=false      ← PENTING: false untuk production
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

#### Step 5: Run Migrations (di server)

```bash
php artisan migrate --force
php artisan db:seed --force
```

#### Step 6: Optimize (di server)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Method 2: Git Deployment

```bash
# Di server via SSH
git clone https://github.com/your-repo/ptsp.git
cd ptsp

# Install dependencies
composer install --no-dev --optimize-autoloader

# Setup environment
cp .env.example .env
php artisan key:generate

# Build assets (jika ada Node.js di server)
npm install
npm run build

# Or upload pre-built assets dari local
# scp -r public/build user@server:/path/to/project/public/

# Run migrations
php artisan migrate --force

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
```

---

## Cara Menggunakan Assets

### Opsi 1: Helper Functions (Recommended untuk Production)

Update layout files untuk menggunakan helper functions:

**File:** `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>

    {{-- Preload critical assets --}}
    {!! assets_preload([
        'resources/css/app.css',
        'resources/js/app.js'
    ]) !!}

    {{-- Load CSS --}}
    {!! asset_css('resources/css/app.css') !!}

    @stack('styles')
</head>
<body>
    @yield('content')

    {{-- Load JS --}}
    {!! asset_js('resources/js/app.js') !!}

    @stack('scripts')
</body>
</html>
```

### Opsi 2: @vite Directive (Works in Both)

Laravel Vite plugin otomatis detect environment:
- **Development:** Load dari Vite dev server
- **Production:** Load dari `public/build/manifest.json`

```blade
<!DOCTYPE html>
<html>
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

### Opsi 3: Manual Asset URLs (Untuk kontrolpenuh)

```blade
<!DOCTYPE html>
<html>
<head>
    @if(app()->environment('local') && file_exists(public_path('hot')))
        {{-- Development with Vite --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Production with helper --}}
        {!! asset_css('resources/css/app.css') !!}
    @endif
</head>
<body>
    @yield('content')

    @if(!app()->environment('local'))
        {!! asset_js('resources/js/app.js') !!}
    @endif
</body>
</html>
```

---

## Helper Functions Reference

### Available Helpers:

```php
// Generate CSS link tag
{!! asset_css('resources/css/app.css') !!}
// Output: <link rel="stylesheet" href="/build/assets/app-[hash].css">

// Generate JS script tag (deferred)
{!! asset_js('resources/js/app.js') !!}
// Output: <script src="/build/assets/app-[hash].js" defer></script>

// Generate JS without defer
{!! asset_js('resources/js/app.js', false) !!}

// Get asset URL only
{{ compiled_asset('resources/css/app.css') }}
// Output: /build/assets/app-[hash].css

// Preload assets
{!! assets_preload([
    'resources/css/app.css',
    'resources/js/app.js'
]) !!}
// Output: <link rel="preload" ...>
```

### How Helpers Work:

1. **Development (dengan Vite running):**
   - Detect Vite dev server di `localhost:5173`
   - Return Vite URLs: `http://localhost:5173/resources/css/app.css`

2. **Production (tanpa Vite):**
   - Read `public/build/manifest.json`
   - Map `resources/css/app.css` → `build/assets/app-[hash].css`
   - Return production URL: `/build/assets/app-[hash].css`

---

## File Structure di Server

```
public_html/  (atau htdocs, www)
├── index.php          ← Laravel entry point
├── .htaccess
├── build/             ← Compiled assets (dari npm run build)
│   ├── manifest.json
│   └── assets/
│       ├── app-*.css
│       ├── app-*.js
│       ├── bootstrap-*.css
│       └── bootstrap-*.js
├── css/
├── js/
└── images/

application/  (di luar public_html - AMAN)
├── app/
├── bootstrap/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env            ← JANGAN di public_html!
└── artisan
```

**Konfigurasi Web Server:**

Point document root ke `/path/to/application/public`, bukan `/path/to/application`.

---

## Troubleshooting

### 1. Assets tidak muncul di production

**Cek:**
```bash
# Apakah manifest ada?
ls public/build/manifest.json

# Apakah assets ada?
ls public/build/assets/
```

**Solusi:**
```bash
# Build ulang
npm run build

# Upload public/build/ ke server
```

### 2. Error "Vite manifest not found"

**Penyebab:** `npm run build` belum dijalankan atau `public/build/` tidak ter-upload

**Solusi:**
```bash
# Di local
npm run build

# Upload public/build/ ke server via FTP
```

### 3. Assets load tapi styling rusak

**Penyebab:** Base URL tidak benar atau .htaccess missing

**Solusi:**

Check `.env`:
```env
APP_URL=https://yourdomain.com  ← Sesuaikan dengan domain
```

Check `.htaccess` di `public/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    RewriteRule ^index\.php$ - [L]
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule . /index.php [L]
</IfModule>
```

### 4. Helper functions tidak work

**Penyebab:** Autoload tidak ter-update

**Solusi:**
```bash
composer dump-autoload
php artisan config:clear
```

### 5. CSS/JS cache di browser

**Solusi:** Hash otomatis berubah setiap build untuk cache busting.

Jika masih cache:
```bash
# Build ulang dengan hash baru
npm run build

# Upload ke server
# Browser akan auto-load versi baru karena hash berbeda
```

---

## Performance Optimization

### 1. Enable Gzip Compression

Tambah di `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

### 2. Browser Caching

Tambah di `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

### 3. Preload Critical Assets

Gunakan preload untuk assets kritikal:
```blade
{!! assets_preload([
    'resources/css/app.css',
    'resources/js/app.js'
]) !!}
```

### 4. Lazy Load Non-Critical JS

```blade
{!! asset_js('resources/js/app.js', true) !!}  ← defer=true (default)
```

---

## Production Checklist

Sebelum deploy:

- [ ] `npm run build` berhasil
- [ ] `public/build/manifest.json` exists
- [ ] `public/build/assets/*.css` exists
- [ ] `public/build/assets/*.js` exists
- [ ] `.env` configured untuk production
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` ter-generate
- [ ] Database configured
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `chmod -R 755 storage bootstrap/cache`
- [ ] Test assets loading di browser
- [ ] Check console (F12) untuk errors
- [ ] Test semua pages
- [ ] Test forms (CSRF)

---

## Update Workflow

Ketika update aplikasi di production:

```bash
# 1. Build di local
npm run build

# 2. Upload files yang berubah
# - app/
# - resources/
# - public/build/  ← PENTING!
# - routes/
# - config/
# - database/migrations (jika ada)

# 3. Di server via SSH
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Test
# Browse ke website, check console untuk errors
```

---

## FAQ

### Q: Apakah perlu install Node.js di server?
**A:** TIDAK! Compile di local, upload hasil build.

### Q: Apakah perlu npm run dev di server?
**A:** TIDAK! Hanya static assets yang di-serve.

### Q: Bagaimana cara update assets?
**A:** Build di local (`npm run build`), upload `public/build/` ke server.

### Q: Hash assets berubah terus, bagaimana handle cache?
**A:** Itu fitur! Hash otomatis force browser load versi baru.

### Q: Bisa pakai CDN untuk assets?
**A:** Bisa, tapi tidak recommended. Local assets lebih secure & cepat.

### Q: Development pakai @vite, production pakai helper, ribet?
**A:** Gunakan satu method saja (helper atau @vite). Keduanya work di production.

---

## Resources

- [Laravel Vite Documentation](https://laravel.com/docs/vite)
- [Vite Documentation](https://vitejs.dev/)
- [Asset Helper Code](app/Helpers/AssetHelper.php)

---

**Dibuat:** {{ now()->format('d F Y') }}
**Versi:** 1.0
**Aplikasi:** PTSP MTsN 2 Kota Malang
