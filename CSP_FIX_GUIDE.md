# Content Security Policy (CSP) Fix Guide

## ✅ Perbaikan yang Sudah Dilakukan

File yang diupdate: `app/Http/Middleware/SecurityHeaders.php`

### CSP Directives yang Diizinkan:

```php
// Script Sources
script-src 'self' 'unsafe-inline' 'unsafe-eval'
          https://cdn.jsdelivr.net
          https://unpkg.com
          https://fonts.bunny.net

// Style Sources
style-src 'self' 'unsafe-inline'
         https://fonts.googleapis.com
         https://fonts.bunny.net
         https://cdn.jsdelivr.net
         https://unpkg.com

// Font Sources
font-src 'self'
        https://fonts.gstatic.com
        https://fonts.bunny.net
        data:

// Image Sources
img-src 'self' data: https: blob:

// Connect Sources (AJAX, WebSocket, etc)
connect-src 'self'
           https://cdn.jsdelivr.net
           https://fonts.bunny.net
           https://fonts.googleapis.com
           https://fonts.gstatic.com

// Worker & Child Sources
worker-src 'self' blob:
child-src 'self' blob:
```

---

## 🔧 Cara Menerapkan Perbaikan

### 1. Clear Application Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Restart Development Server
```bash
# Stop server (Ctrl+C)
# Start server again
php artisan serve
```

### 3. Clear Browser Cache
- **Chrome/Edge:** Ctrl + Shift + Delete
- **Firefox:** Ctrl + Shift + Delete
- **Hard Reload:** Ctrl + Shift + R (semua browser)

### 4. Test Kembali
- Refresh halaman (Ctrl + F5)
- Periksa Console (F12) untuk error CSP
- Pastikan tidak ada error "violates CSP directive"

---

## 🚨 Jika Masih Ada Error CSP

### Opsi 1: Identifikasi URL yang Diblokir

1. Buka Developer Tools (F12)
2. Lihat tab Console
3. Catat URL yang diblokir, contoh:
   ```
   Loading stylesheet 'https://example.com/style.css' violates CSP
   ```
4. Tambahkan domain tersebut ke CSP

### Opsi 2: Tambah Domain ke CSP

Edit file: `app/Http/Middleware/SecurityHeaders.php`

```php
// Tambahkan domain baru di directive yang sesuai
$response->headers->set('Content-Security-Policy',
    "default-src 'self'; " .
    "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://DOMAIN-BARU.com; " .
    "style-src 'self' 'unsafe-inline' https://DOMAIN-BARU.com; " .
    // ... dst
);
```

### Opsi 3: Disable CSP untuk Development (Temporary)

**HANYA UNTUK DEVELOPMENT - JANGAN UNTUK PRODUCTION!**

Edit file: `app/Http/Middleware/SecurityHeaders.php`

```php
public function handle(Request $request, Closure $next)
{
    $response = $next($request);

    // Temporary disable CSP for development
    if (config('app.env') === 'local') {
        // Comment out CSP
        // $response->headers->set('Content-Security-Policy', ...);
    } else {
        // Enable CSP in production
        $response->headers->set('Content-Security-Policy',
            // ... CSP directives
        );
    }

    // ... other headers
    return $response;
}
```

### Opsi 4: Gunakan Report-Only Mode (Recommended untuk Testing)

Ubah dari `Content-Security-Policy` ke `Content-Security-Policy-Report-Only`:

```php
// Report violations tanpa blocking
$response->headers->set('Content-Security-Policy-Report-Only',
    "default-src 'self'; " .
    // ... directives
);
```

Ini akan:
- ✅ Melaporkan violations di console
- ✅ Tidak memblokir resource
- ✅ Membantu debugging

---

## 📝 Common CDN yang Perlu Diizinkan

### Bootstrap
```
https://cdn.jsdelivr.net/npm/bootstrap@*
https://stackpath.bootstrapcdn.com
```

### Tailwind CSS
```
https://cdn.tailwindcss.com
https://unpkg.com
```

### Alpine.js
```
https://cdn.jsdelivr.net/npm/alpinejs@*
https://unpkg.com/alpinejs@*
```

### jQuery
```
https://code.jquery.com
https://ajax.googleapis.com/ajax/libs/jquery/
```

### Font Awesome
```
https://cdnjs.cloudflare.com/ajax/libs/font-awesome/
https://use.fontawesome.com
```

### Google Fonts
```
https://fonts.googleapis.com
https://fonts.gstatic.com
```

### Livewire (jika digunakan)
```
connect-src 'self' wss://yourdomain.com
```

---

## 🔍 Debugging CSP Violations

### Method 1: Chrome DevTools
1. Buka DevTools (F12)
2. Tab "Console"
3. Filter by "CSP"
4. Lihat URL dan directive yang diblokir

### Method 2: CSP Evaluator
1. Kunjungi: https://csp-evaluator.withgoogle.com/
2. Paste CSP policy Anda
3. Lihat rekomendasi

### Method 3: Report URI (Advanced)
Tambah reporting endpoint:

```php
$response->headers->set('Content-Security-Policy',
    "default-src 'self'; " .
    // ... other directives
    "report-uri /csp-violation-report-endpoint;"
);
```

Buat route untuk menerima report:
```php
Route::post('/csp-violation-report-endpoint', function (Request $request) {
    Log::warning('CSP Violation', $request->all());
    return response()->json(['status' => 'reported']);
});
```

---

## ⚖️ Balance Security vs Functionality

### Strict CSP (Production - High Security)
```php
"script-src 'self' 'nonce-{random}'; " .  // No unsafe-inline
"style-src 'self' 'nonce-{random}'; " .   // No unsafe-inline
"default-src 'self';"
```

### Moderate CSP (Current Implementation)
```php
"script-src 'self' 'unsafe-inline' 'unsafe-eval' https://trusted-cdn.com; " .
"style-src 'self' 'unsafe-inline' https://trusted-cdn.com; "
```

### Permissive CSP (Development Only)
```php
"default-src *; " .
"script-src * 'unsafe-inline' 'unsafe-eval'; " .
"style-src * 'unsafe-inline';"
```

---

## 🎯 Rekomendasi

### Untuk Development:
1. ✅ Gunakan CSP yang sudah diperbaiki
2. ✅ Atau gunakan Report-Only mode
3. ✅ Monitor console untuk violations
4. ❌ Jangan disable CSP sepenuhnya

### Untuk Production:
1. ✅ Gunakan CSP strict
2. ✅ Hapus 'unsafe-inline' jika memungkinkan
3. ✅ Gunakan nonce untuk inline scripts
4. ✅ Whitelist hanya CDN yang diperlukan
5. ✅ Monitor CSP reports
6. ✅ Regular security audit

---

## 📚 Resources

- [MDN CSP Guide](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP)
- [CSP Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Content_Security_Policy_Cheat_Sheet.html)
- [Google CSP Guide](https://developers.google.com/web/fundamentals/security/csp)
- [CSP Evaluator](https://csp-evaluator.withgoogle.com/)

---

## 🆘 Troubleshooting Checklist

- [ ] Clear application cache (`php artisan cache:clear`)
- [ ] Clear browser cache (Ctrl+Shift+Delete)
- [ ] Hard reload page (Ctrl+Shift+R)
- [ ] Check Console for specific violations (F12)
- [ ] Verify CDN URLs are whitelisted
- [ ] Test in incognito/private mode
- [ ] Try different browser
- [ ] Check if middleware is registered in `bootstrap/app.php`
- [ ] Restart development server

---

**Catatan:** Jika setelah perbaikan ini masih ada error CSP, kirimkan screenshot error dari Console (F12) untuk diagnostic lebih lanjut.
