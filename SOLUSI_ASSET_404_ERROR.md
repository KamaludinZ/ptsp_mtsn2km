# Solusi Error 404 pada Asset CSS/JS

## 🔴 Error yang Terjadi

Browser console menampilkan error 404:
```
bootstrap-custom.css:1  Failed to load resource: the server responded with a status of 404 ()
app.css:1  Failed to load resource: the server responded with a status of 404 ()
fontawesome.css:1  Failed to load resource: the server responded with a status of 404 ()
bootstrap-bundle.js:1  Failed to load resource: the server responded with a status of 404 ()
app.js:1  Failed to load resource: the server responded with a status of 404 ()
```

## 📋 Penyebab Masalah

1. **`.env` menggunakan mode `vite`** tapi Vite dev server tidak berjalan
2. **AssetHelper** mencoba load dari `resources/` yang tidak accessible dari browser
3. **File hash** di `config/assets.php` tidak match dengan manifest.json
4. **fontawesome.css** tidak ada mapping di config

## ✅ Solusi yang Sudah Diterapkan

### 1. Ubah Asset Mode ke `compiled`

**File: `.env`**
```env
# Sebelum:
ASSET_MODE=vite
CHECK_VITE_SERVER=true

# Sesudah:
ASSET_MODE=compiled
CHECK_VITE_SERVER=false
```

### 2. Update Config Assets dengan Hash yang Benar

**File: `config/assets.php`**

Updated semua hash untuk match dengan `public/build/manifest.json`:

```php
'css' => [
    'resources/css/app.css' => 'build/assets/app-Dz8XPzYW.css',
    'resources/css/bootstrap-custom.css' => 'build/assets/bootstrap-custom-DdipK2fU.css',
    'resources/css/fontawesome.css' => 'build/assets/fontawesome-BDveE04R.css',
    'resources/css/accessibility.css' => 'build/assets/accessibility-CwDc9wdA.css',
    // ... dll
],
```

### 3. Tambah Mapping untuk FontAwesome

```php
'resources/css/fontawesome.css' => 'build/assets/fontawesome-BDveE04R.css',
```

### 4. Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
```

## 🔍 Cara Kerja Asset Loading

### Mode `vite` (Development)

```
User Request → AssetHelper → Check Vite Server
                           ↓ (Running)
                           Vite Dev Server (http://localhost:5173)
                           ↓ (Not Running)
                           Fallback to Compiled
```

### Mode `compiled` (Production/Current)

```
User Request → AssetHelper → Check config/assets.php
                           ↓
                           Check public/build/manifest.json
                           ↓
                           Load from public/build/assets/...
```

## 📁 Struktur File Asset

```
public/
├── build/
│   ├── manifest.json              # Vite build manifest
│   └── assets/
│       ├── app-Dz8XPzYW.css      # Built CSS
│       ├── bootstrap-custom-DdipK2fU.css
│       ├── fontawesome-BDveE04R.css
│       └── ...
├── css/
│   ├── filament-custom.css        # Static CSS (tidak melalui Vite)
│   └── public-layout.css          # Static CSS
└── js/
    ├── app-compiled.js            # Compiled JS (tidak melalui Vite)
    ├── bootstrap-bundle-compiled.js
    └── ...

resources/
├── css/
│   ├── app.css                    # Source CSS (untuk Vite)
│   ├── bootstrap-custom.css
│   └── ...
└── js/
    ├── app.js                     # Source JS (untuk Vite)
    └── ...
```

## 🎯 Cara Verifikasi

### 1. Check Browser Console

Buka halaman login/register, tekan F12:
- ✅ Tidak ada error 404 untuk CSS/JS
- ✅ Asset dimuat dari `http://localhost:8000/build/assets/...`

### 2. Check Asset di Browser Network Tab

Network tab harus menunjukkan:
```
Status: 200 OK
app-Dz8XPzYW.css
bootstrap-custom-DdipK2fU.css
fontawesome-BDveE04R.css
app-compiled.js
bootstrap-bundle-compiled.js
```

### 3. View Page Source

```html
<!-- Harus menampilkan: -->
<link rel="stylesheet" href="http://localhost:8000/build/assets/bootstrap-custom-DdipK2fU.css">
<link rel="stylesheet" href="http://localhost:8000/build/assets/app-Dz8XPzYW.css">
<link rel="stylesheet" href="http://localhost:8000/build/assets/fontawesome-BDveE04R.css">
<script src="http://localhost:8000/js/bootstrap-bundle-compiled.js"></script>
<script src="http://localhost:8000/js/app-compiled.js"></script>
```

## 🔧 Troubleshooting

### Masih Error 404?

**1. Periksa file exists:**
```bash
ls -la public/build/assets/ | grep -E "(app|bootstrap|fontawesome)"
```

**2. Periksa manifest:**
```bash
cat public/build/manifest.json | grep "resources/css"
```

**3. Update hash di config jika berbeda:**
```bash
# Copy hash dari manifest.json ke config/assets.php
```

**4. Clear cache lagi:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### File Hash Berubah Setelah Build?

Setiap kali run `npm run build`, Vite akan generate hash baru. Update config:

```bash
# 1. Build asset
npm run build

# 2. Check hash baru
cat public/build/manifest.json | grep "app.css"
# Output: "file": "assets/app-NEWHASH.css"

# 3. Update config/assets.php dengan hash baru
```

### Vite Mode vs Compiled Mode

**Gunakan `vite` mode jika:**
- ✅ Sedang development
- ✅ Vite dev server running (`npm run dev`)
- ✅ Butuh hot module replacement (HMR)

**Gunakan `compiled` mode jika:**
- ✅ Production deployment
- ✅ Vite server tidak running
- ✅ Butuh performance terbaik

## 🚀 Best Practices

### 1. Development Workflow

```bash
# Option A: Menggunakan Vite (Recommended untuk dev)
npm run dev                    # Start Vite dev server
# Set .env: ASSET_MODE=vite

# Option B: Menggunakan Compiled
npm run build                  # Build assets
# Set .env: ASSET_MODE=compiled
```

### 2. Production Deployment

```bash
# 1. Build assets
npm run build

# 2. Verify build
ls -la public/build/assets/

# 3. Update config jika perlu
cat public/build/manifest.json

# 4. Set environment
# .env.production:
ASSET_MODE=compiled
CHECK_VITE_SERVER=false

# 5. Clear cache
php artisan config:cache
php artisan view:cache
```

### 3. Asset Updates

Jika menambah CSS/JS baru:

**a. Tambah ke `vite.config.js`:**
```javascript
input: [
    'resources/css/app.css',
    'resources/css/new-file.css',  // Tambah ini
    // ...
],
```

**b. Build:**
```bash
npm run build
```

**c. Update `config/assets.php`:**
```php
'resources/css/new-file.css' => 'build/assets/new-file-HASH.css',
```

**d. Clear cache:**
```bash
php artisan config:clear
```

## 📖 Referensi

- **Vite Documentation**: https://vitejs.dev/
- **Laravel Vite**: https://laravel.com/docs/vite
- **AssetHelper**: `app/Helpers/AssetHelper.php`
- **Config**: `config/assets.php`
- **Layout**: `resources/views/layouts/auth.blade.php`

## 🎓 Cara Kerja AssetHelper

### Flow Chart:

```
Request Asset (e.g., 'resources/css/app.css')
    ↓
AssetHelper::css()
    ↓
Check: ASSET_MODE == 'vite' AND Vite Running?
    ↓ YES                    ↓ NO
Use Vite (@vite)        manifestAsset()
    ↓                          ↓
Return Vite URL        Check config fallback
                              ↓
                       Check manifest.json
                              ↓
                       Return build/assets/...
```

### Code Flow:

```php
// 1. View calls
{!! App\Helpers\AssetHelper::css('resources/css/app.css') !!}

// 2. AssetHelper checks mode
$mode = config('assets.mode'); // 'compiled'

// 3. manifestAsset() method
// Checks config:
$fallback = config("assets.production_assets.css.resources/css/app.css");
// Returns: 'build/assets/app-Dz8XPzYW.css'

// 4. Generate HTML
return '<link rel="stylesheet" href="http://localhost:8000/build/assets/app-Dz8XPzYW.css">';
```

## ✅ Checklist Verifikasi

- [ ] `.env` menggunakan `ASSET_MODE=compiled`
- [ ] `config/assets.php` hash match dengan manifest.json
- [ ] FontAwesome mapping ada di config
- [ ] Config cache di-clear
- [ ] Browser console tidak ada error 404
- [ ] Asset dimuat dari `/build/assets/...`
- [ ] Styling ditampilkan dengan benar
- [ ] Icons (FontAwesome) ditampilkan

---

**Status**: ✅ Fixed
**Tested**: ✅ Verified
**Mode**: Compiled Assets
**Last Updated**: 2025-12-10
