# Panduan Deployment PTSP MTsN 2 Kota Malang

## Development (Lokal)

### Persiapan Awal

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Setup Environment**
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **Konfigurasi Database**
   Edit file `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_DATABASE=ptspmtsn2
   DB_USERNAME=ptspmtsn2
   DB_PASSWORD=your_password

   ASSET_MODE=vite
   CHECK_VITE_SERVER=true
   ```

4. **Jalankan Migrasi**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

### Menjalankan Development Server

#### Opsi 1: Menggunakan Script Batch (Recommended - Windows)
```bash
dev-run.bat
```

Script ini akan otomatis:
- Check dependencies
- Clear cache
- Menjalankan Laravel serve di http://localhost:8000
- Menjalankan Vite dev server dengan HMR di http://localhost:5173

#### Opsi 2: Manual (Dua Terminal)

**Terminal 1 - Laravel:**
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

**Terminal 2 - Vite:**
```bash
npm run dev
```

### Troubleshooting Development

#### Error: NS_ERROR_NET_INTERRUPT atau Mixed Content

Jika browser mencoba akses HTTPS padahal server di HTTP:

1. **Clear Browser Cache dan HSTS:**
   - Firefox: Settings → Privacy & Security → Clear Data → Check "Site Settings"
   - Chrome: chrome://net-internals/#hsts → Delete domain "localhost"

2. **Pastikan .env sudah benar:**
   ```env
   APP_ENV=local
   APP_URL=http://localhost:8000
   ASSET_MODE=vite
   ```

3. **Clear Laravel Cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```

4. **Restart server** menggunakan `dev-run.bat` atau restart manual

## Production Build

### Build Assets untuk Production

#### Menggunakan Script Batch (Recommended - Windows)
```bash
build-for-production.bat
```

Script ini akan:
1. Install dependencies dengan optimasi production
2. Clear semua cache
3. Build assets dengan Vite (minify, optimize, tree-shaking)
4. Cache konfigurasi Laravel
5. (Opsional) Jalankan migrasi

#### Manual Build

```bash
# 1. Install dependencies
composer install --optimize-autoloader --no-dev
npm ci --production=false

# 2. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 3. Build assets
npm run build

# 4. Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Deploy ke Server Production

1. **Upload Files**
   Upload semua file KECUALI:
   - `node_modules/`
   - `.env` (buat baru di server)
   - `storage/` (buat struktur folder, jangan upload isinya)
   - `.git/`

2. **Setup Environment di Server**
   ```bash
   cp .env.production.example .env
   nano .env
   ```

   Sesuaikan:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ASSET_MODE=build
   CHECK_VITE_SERVER=false

   # Database production
   DB_CONNECTION=pgsql
   DB_DATABASE=production_db
   DB_USERNAME=production_user
   DB_PASSWORD=secure_password
   ```

3. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

4. **Set Permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

5. **Install Dependencies (jika belum)**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate --force
   ```

7. **Cache Optimization**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

8. **Setup Web Server**

   **Nginx Configuration:**
   ```nginx
   server {
       listen 80;
       listen [::]:80;
       server_name your-domain.com;
       return 301 https://$server_name$request_uri;
   }

   server {
       listen 443 ssl http2;
       listen [::]:443 ssl http2;
       server_name your-domain.com;
       root /path/to/ptsp/public;

       ssl_certificate /path/to/cert.pem;
       ssl_certificate_key /path/to/key.pem;

       add_header X-Frame-Options "SAMEORIGIN";
       add_header X-Content-Type-Options "nosniff";

       index index.php;

       charset utf-8;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location = /favicon.ico { access_log off; log_not_found off; }
       location = /robots.txt  { access_log off; log_not_found off; }

       error_page 404 /index.php;

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

   **Apache Configuration (.htaccess sudah ada di public/)**
   Pastikan mod_rewrite enabled:
   ```bash
   a2enmod rewrite
   systemctl restart apache2
   ```

## Mode Asset

Aplikasi ini mendukung 2 mode asset:

### 1. Mode Vite (Development)
```env
ASSET_MODE=vite
CHECK_VITE_SERVER=true
```
- Hot Module Replacement (HMR)
- Fast refresh saat development
- Membutuhkan Vite dev server berjalan

### 2. Mode Build (Production)
```env
ASSET_MODE=build
CHECK_VITE_SERVER=false
```
- Assets sudah di-bundle dan di-minify
- Optimal untuk production
- Tidak membutuhkan Vite dev server

## Checklist Deployment

### Pre-Deployment
- [ ] Build assets dengan `build-for-production.bat`
- [ ] Test build di lokal dengan `ASSET_MODE=build`
- [ ] Update .env.production.example dengan konfigurasi terbaru
- [ ] Commit dan push ke repository

### Deployment
- [ ] Upload files ke server
- [ ] Setup .env production
- [ ] Generate APP_KEY
- [ ] Set folder permissions
- [ ] Install composer dependencies
- [ ] Run migrations
- [ ] Cache optimization
- [ ] Test semua fitur
- [ ] Check SSL certificate
- [ ] Verify HTTPS redirect

### Post-Deployment
- [ ] Monitor error logs
- [ ] Check performance
- [ ] Verify database connections
- [ ] Test email functionality
- [ ] Verify file uploads work

## Troubleshooting Production

### Assets tidak load
```bash
# Check build folder exists
ls -la public/build

# Rebuild if needed
npm run build

# Verify .env
grep ASSET_MODE .env  # Should be 'build'
```

### 500 Internal Server Error
```bash
# Check storage permissions
chmod -R 755 storage bootstrap/cache

# Clear cache
php artisan config:clear
php artisan cache:clear

# Check error logs
tail -f storage/logs/laravel.log
```

### Mixed Content Warnings
Pastikan .env production menggunakan HTTPS:
```env
APP_URL=https://your-domain.com
```

## Support

Untuk pertanyaan atau masalah, hubungi tim development.
