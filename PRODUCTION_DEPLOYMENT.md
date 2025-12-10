# PTSP MTsN 2 Kota Malang - Production Deployment Guide

## PostgreSQL Database Setup

### Prerequisites
- PostgreSQL 12 or higher
- PHP 8.2 or higher with `pdo_pgsql` extension enabled
- Composer
- Web server (Apache/Nginx)

### Database Configuration

1. Create PostgreSQL database:
   ```sql
   CREATE DATABASE ptspmtsn2;
   CREATE USER ptspmtsn2 WITH PASSWORD 'your_secure_password_here';
   GRANT ALL PRIVILEGES ON DATABASE ptspmtsn2 TO ptspmtsn2;
   ```

2. Update your `.env` file:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=ptspmtsn2
   DB_USERNAME=ptspmtsn2
   DB_PASSWORD=your_secure_password_here
   DB_TIMEOUT=30
   DB_PERSISTENT=false
   DB_SSLMODE=prefer
   ```

### Production Deployment Steps

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd PTSP-MTsN-2-KOTA-MALANG
   ```

2. **Install dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

3. **Set up environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your production settings
   php artisan key:generate
   ```

4. **Run database migrations:**
   ```bash
   php artisan migrate --force
   ```

5. **Build assets for production (without Vite):**
   ```bash
   npm install --production=false  # Install dev dependencies for build
   npm run build                  # Build assets using Vite in production mode
   ```

6. **Set proper file permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 bootstrap/cache/
   chmod -R 644 .env
   ```

7. **Run the production build script:**
   ```bash
   # On Windows
   build-production.bat

   # On Linux/Unix
   # Use the Windows batch file or create a shell script that follows the same steps
   ```

8. **Configure environment for production asset loading:**
   Set these values in your .env file:
   ```
   APP_ENV=production
   ASSET_MODE=compiled
   CHECK_VITE_SERVER=false
   ```

### Production Security Settings

- HTTPS is enforced in production
- Security headers are set via `.htaccess`
- Session security is configured
- Database connections are secured with SSL
- File upload restrictions are in place

### PostgreSQL Optimizations

- JSON columns use GIN indexes for better performance
- PostgreSQL extensions enabled: `uuid-ossp`, `pg_trgm`, `btree_gin`
- Connection pooling is configurable via environment variables

### Web Server Configuration

#### Apache (.htaccess included)

The application includes a production-ready `.htaccess` file with:
- Security headers
- HTTPS enforcement
- Compression
- Caching headers
- File access restrictions

#### Nginx (example configuration)

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    root /path/to/PTSP-MTsN-2-KOTA-MALANG/public;
    index index.php;

    ssl_certificate /path/to/ssl/cert.pem;
    ssl_certificate_key /path/to/ssl/private.key;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Maintenance Mode

To enable/disable maintenance mode:
```bash
# Enable maintenance mode
php artisan down

# Disable maintenance mode
php artisan up
```

### Monitoring and Health Checks

The application supports health checks at `/up` endpoint. This endpoint returns 200 status when the application is healthy and 503 when in maintenance mode.

### Environment Variables for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ptspmtsn2
DB_USERNAME=ptspmtsn2
DB_PASSWORD=your_secure_password_here

# Production-specific settings
DB_TIMEOUT=30
DB_PERSISTENT=false
DB_SSLMODE=prefer

# Asset configuration for production (no Vite)
ASSET_MODE=compiled
CHECK_VITE_SERVER=false

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Security
SESSION_SECURE_COOKIE=true
```

### Troubleshooting

1. **Database connection issues:**
   - Ensure PostgreSQL service is running
   - Verify database credentials in `.env`
   - Check that PostgreSQL extensions are installed

2. **Permission errors:**
   - Verify storage and bootstrap/cache directories are writable
   - Ensure proper file permissions are set

3. **SSL/HTTPS issues:**
   - Check SSL certificates are properly installed
   - Verify .htaccess rules are working
   - Confirm APP_URL uses HTTPS

### Updating in Production

1. Backup database and files
2. Pull latest code changes
3. Run `composer install` (if dependencies changed)
4. Run `php artisan migrate` (if database changes)
5. Clear caches: `php artisan config:clear` and `php artisan cache:clear`
6. Rebuild assets if needed: `npm run build`

---

For support and questions, contact the development team.