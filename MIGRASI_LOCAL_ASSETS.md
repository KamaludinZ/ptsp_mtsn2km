# Panduan Migrasi: CDN ke Local Assets

## 📋 Daftar Isi
1. [Overview](#overview)
2. [Perubahan yang Dilakukan](#perubahan-yang-dilakukan)
3. [Langkah Instalasi](#langkah-instalasi)
4. [Update Layout Files](#update-layout-files)
5. [Testing](#testing)
6. [Troubleshooting](#troubleshooting)

---

## Overview

Aplikasi telah diubah dari menggunakan CDN external (cdn.jsdelivr.net, fonts.googleapis.com, dll) menjadi menggunakan local assets. Ini memberikan beberapa keuntungan:

✅ **Keamanan Lebih Baik** - Tidak bergantung pada CDN pihak ketiga
✅ **Performa Lebih Cepat** - Assets di-host locally
✅ **Privacy** - Tidak ada request ke server external
✅ **Offline Support** - Aplikasi bisa berjalan tanpa internet
✅ **CSP Compliance** - Content Security Policy lebih strict

---

## Perubahan yang Dilakukan

### 1. File yang Ditambahkan

```
resources/
├── css/
│   └── bootstrap-custom.css          ← NEW: Bootstrap CSS bundle
└── js/
    └── bootstrap-bundle.js            ← NEW: Bootstrap JS bundle
```

### 2. File yang Diupdate

```
✓ package.json                        ← Added Bootstrap dependencies
✓ vite.config.js                      ← Added new entry points
✓ resources/css/app.css               ← Added custom styles
✓ app/Http/Middleware/SecurityHeaders.php  ← Strict CSP (no CDN)
```

### 3. Dependencies yang Ditambahkan

```json
{
  "dependencies": {
    "bootstrap": "^5.3.2",
    "@popperjs/core": "^2.11.8",
    "bootstrap-icons": "^1.11.3"
  }
}
```

---

## Langkah Instalasi

### Step 1: Install Node Dependencies

```bash
npm install
```

Ini akan menginstall:
- Bootstrap 5.3.2
- Popper.js (dependency Bootstrap)
- Bootstrap Icons

### Step 2: Build Assets

#### Development Mode (dengan hot reload)
```bash
npm run dev
```

#### Production Mode (optimized)
```bash
npm run build
```

### Step 3: Verify Build

Check bahwa file-file ini sudah ter-generate di `public/build/`:
```
public/build/
├── manifest.json
└── assets/
    ├── app-[hash].css
    ├── app-[hash].js
    ├── bootstrap-custom-[hash].css
    └── bootstrap-bundle-[hash].js
```

---

## Update Layout Files

Anda perlu mengupdate semua layout files untuk menggunakan `@vite` directive instead of CDN links.

### Layout Files yang Perlu Diupdate:

1. `resources/views/layouts/app.blade.php`
2. `resources/views/layouts/public.blade.php`
3. `resources/views/layouts/guest.blade.php`
4. `resources/views/errors/layout.blade.php`
5. `resources/views/onlineportal/layout.blade.php`
6. `resources/views/supervision/layout.blade.php`
7. `resources/views/backoffice/layout.blade.php`
8. `resources/views/frontdesk/layout.blade.php`

### Contoh Update: layouts/app.blade.php

#### ❌ SEBELUM (dengan CDN):
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS dari CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    @yield('content')

    <!-- Bootstrap JS dari CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

#### ✅ SESUDAH (dengan Vite):
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

### Untuk Layouts yang Menggunakan Bootstrap:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Vite Assets with Bootstrap -->
    @vite([
        'resources/css/bootstrap-custom.css',
        'resources/js/bootstrap-bundle.js',
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>
<body>
    @yield('content')
</body>
</html>
```

### Untuk Layouts yang Hanya Menggunakan Tailwind:

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Vite Assets with Tailwind only -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
```

---

## Contoh Update untuk Semua Layout Files

### 1. layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('layouts.navigation')

    <main>
        @yield('content')
    </main>
</body>
</html>
```

### 2. layouts/public.blade.php (dengan Bootstrap)

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PTSP MTsN 2 Kota Malang')</title>

    @vite([
        'resources/css/bootstrap-custom.css',
        'resources/js/bootstrap-bundle.js',
        'resources/css/app.css',
        'resources/js/app.js'
    ])
</head>
<body>
    @yield('content')
</body>
</html>
```

### 3. errors/layout.blade.php

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Inline critical CSS for error pages */
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        /* ... rest of inline styles ... */
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
```

---

## Testing

### 1. Test Development Server

```bash
# Terminal 1: Start Vite dev server
npm run dev

# Terminal 2: Start Laravel server
php artisan serve
```

Visit: `http://localhost:8000`

### 2. Test Asset Loading

1. Buka Developer Tools (F12)
2. Tab "Network"
3. Reload halaman
4. Verify bahwa semua assets loaded dari `/build/assets/` bukan CDN

**Expected:**
```
✓ /build/assets/app-[hash].css
✓ /build/assets/app-[hash].js
✓ /build/assets/bootstrap-custom-[hash].css
✓ /build/assets/bootstrap-bundle-[hash].js
```

**NOT:**
```
✗ https://cdn.jsdelivr.net/...
✗ https://fonts.googleapis.com/...
```

### 3. Test CSP (Content Security Policy)

1. Buka Console (F12)
2. Cari CSP violations
3. Seharusnya TIDAK ada error seperti:
   - ❌ "violates Content Security Policy directive"
   - ❌ "Loading stylesheet from CDN..."

### 4. Test Bootstrap Components

Pastikan komponen Bootstrap berfungsi:
- ✓ Modals
- ✓ Dropdowns
- ✓ Tooltips
- ✓ Popovers
- ✓ Collapse

### 5. Test Production Build

```bash
# Build for production
npm run build

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Test
php artisan serve
```

---

## Troubleshooting

### Issue 1: "Vite manifest not found"

**Error:**
```
Vite manifest not found at: public/build/manifest.json
```

**Solution:**
```bash
# Build assets
npm run build

# Or run dev server
npm run dev
```

### Issue 2: "Module not found: bootstrap"

**Error:**
```
Module not found: Can't resolve 'bootstrap'
```

**Solution:**
```bash
# Install dependencies
npm install

# Clear npm cache if needed
npm cache clean --force
npm install
```

### Issue 3: Styles tidak muncul

**Solution:**
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Rebuild assets
npm run build

# Hard reload browser (Ctrl+Shift+R)
```

### Issue 4: Bootstrap JavaScript tidak bekerja

**Check:**
1. Apakah `bootstrap-bundle.js` sudah di-import di layout?
2. Apakah ada error di Console (F12)?
3. Apakah `window.bootstrap` defined?

**Test di Console:**
```javascript
// Cek apakah Bootstrap loaded
console.log(window.bootstrap);

// Test Bootstrap Modal
const modal = new bootstrap.Modal('#myModal');
modal.show();
```

### Issue 5: Hot Module Replacement (HMR) tidak bekerja

**Solution:**
```bash
# Stop dev server
# Clear node_modules
rm -rf node_modules
npm install

# Restart dev server
npm run dev
```

### Issue 6: Build size terlalu besar

**Optimization:**

Edit `vite.config.js`:
```javascript
export default defineConfig({
    plugins: [laravel({...})],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'bootstrap': ['bootstrap'],
                    'vendor': ['axios', 'alpinejs']
                }
            }
        },
        chunkSizeWarningLimit: 1000
    }
});
```

---

## Performance Comparison

### Before (dengan CDN):
- ❌ External requests: 5-10
- ❌ Load time: 2-3s (tergantung CDN)
- ❌ CSP: Permissive (security risk)
- ❌ Privacy: Data ke third-party

### After (dengan Local Assets):
- ✅ External requests: 0
- ✅ Load time: 0.5-1s (local)
- ✅ CSP: Strict (secure)
- ✅ Privacy: No third-party

---

## Keuntungan Security

### CSP Sebelum (Permissive):
```
script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net
```

### CSP Sesudah (Strict):
```
script-src 'self' 'unsafe-inline'
style-src 'self' 'unsafe-inline'
font-src 'self' data:
```

**Benefit:**
- ✅ Reduced attack surface
- ✅ No CDN hijacking risk
- ✅ Better privacy
- ✅ GDPR compliant

---

## Production Checklist

Sebelum deploy ke production:

- [ ] `npm install` berhasil
- [ ] `npm run build` berhasil
- [ ] Semua layout files sudah diupdate ke `@vite`
- [ ] CSP tidak ada violations
- [ ] Test semua halaman
- [ ] Test Bootstrap components
- [ ] Clear all caches
- [ ] Verify `public/build/` directory exists
- [ ] Commit `public/build/manifest.json` (jika perlu)
- [ ] Update `.gitignore` (exclude `public/build/` jika tidak perlu)

---

## Script Helpers

Buat file `update-layouts.sh` untuk automasi:

```bash
#!/bin/bash

# Update all layout files
find resources/views -name "*.blade.php" -type f -exec sed -i \
    's|<link.*cdn\.jsdelivr\.net.*>||g' {} \;

find resources/views -name "*.blade.php" -type f -exec sed -i \
    's|<link.*fonts\.googleapis\.com.*>||g' {} \;

find resources/views -name "*.blade.php" -type f -exec sed -i \
    's|<script.*cdn\.jsdelivr\.net.*></script>||g' {} \;

echo "CDN links removed from all layouts!"
```

---

## Resources

- [Vite Documentation](https://vitejs.dev/)
- [Laravel Vite Plugin](https://laravel.com/docs/vite)
- [Bootstrap 5 Documentation](https://getbootstrap.com/docs/5.3/)
- [Content Security Policy Guide](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)

---

## Support

Jika ada masalah, check:
1. Console (F12) untuk JavaScript errors
2. Network tab untuk failed requests
3. Laravel logs: `storage/logs/laravel.log`
4. Vite errors di terminal

**Dibuat:** {{ now()->format('d F Y') }}
**Versi:** 1.0
**Aplikasi:** PTSP MTsN 2 Kota Malang
