# Production Deployment Package Summary

## Package Contents

This production deployment package includes:

### 1. Subdomain Root Directory (`subdomain-root/`)
Contains all files that must be placed in the web-accessible directory for your subdomain:
- `index.php` - Laravel front controller (modified for shared hosting)
- `css/`, `js/`, `images/` - Public assets
- `build/` - Compiled production assets (CSS/JS with manifest)
- `.htaccess` - Apache configuration for routing and security
- Other public assets (favicon, manifest, etc.)

### 2. Application Root Directory (`application-root/`)
Contains all sensitive application files that must be placed OUTSIDE the web-accessible directory:
- `app/` - Application source code
- `bootstrap/` - Bootstrap files
- `config/` - Configuration files
- `database/` - Migrations, seeds, and factories
- `resources/` - Views, raw assets
- `routes/` - Route definitions
- `storage/` - File storage and cache (needs write permissions)
- `vendor/` - Composer dependencies (will be created during installation)
- `artisan` - Laravel command-line tool
- `.env` - Environment configuration (sample provided)

### 3. Documentation
- `README.md` - Installation instructions
- `DEPLOYMENT_INSTRUCTIONS.md` - Detailed deployment guide
- `INSTALL.bat` - Installation script for Windows

## Deployment Steps

1. Upload `subdomain-root` contents to your subdomain's web directory
2. Upload the entire `application-root` directory to a non-web-accessible location
3. Update the `.env` file with your server-specific settings
4. Run `composer install --no-dev --optimize-autoloader` in the application root
5. Generate app key: `php artisan key:generate`
6. Run migrations: `php artisan migrate --force`
7. Optimize: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`

## Important Notes

- The `index.php` in `subdomain-root` is configured to point to the application root directory
- Sensitive files are kept outside the web root for security
- The application is pre-configured to work without Vite in production
- Asset handling is configured to use the compiled assets in the build directory
- File permissions must be properly set for the storage and bootstrap/cache directories