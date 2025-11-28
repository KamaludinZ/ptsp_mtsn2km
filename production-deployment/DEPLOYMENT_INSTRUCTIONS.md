# Production Deployment Instructions

## Pre-deployment Checklist

1. Update the `.env` file in the application root with your specific settings:
   - Database credentials
   - Mail settings
   - App URL
   - Any other environment-specific variables

2. Make sure the following directories exist and are writable by the web server:
   - `storage/`
   - `bootstrap/cache/`

## Deployment Steps

1. Upload all files to your shared hosting:
   - Upload the contents of `subdomain-root` to your subdomain's web root (e.g., public_html/subdomain/)
   - Upload the entire `application-root` directory to a non-web-accessible location (e.g., one level above web root)

2. Install PHP dependencies:
   - Navigate to the application root directory
   - Run: `composer install --no-dev --optimize-autoloader`

3. Generate application key:
   - Run: `php artisan key:generate`

4. Run database migrations:
   - Run: `php artisan migrate --force`

5. Clear and cache configuration:
   - Run: `php artisan config:clear`
   - Run: `php artisan config:cache`
   - Run: `php artisan route:clear`
   - Run: `php artisan route:cache`
   - Run: `php artisan view:clear`
   - Run: `php artisan event:clear`
   - Run: `php artisan event:cache`

## Directory Structure After Deployment

Your shared hosting should look like this:
```
/home/username/
├── application-root/              # Non-web accessible
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── artisan
│   ├── composer.json
│   ├── composer.lock
│   └── .env
└── public_html/subdomain/         # Web accessible
    ├── css/
    ├── images/
    ├── js/
    ├── build/
    ├── index.php
    ├── .htaccess
    ├── favicon.ico
    └── ...
```

## File Permissions

Set appropriate file permissions:
- Directories: 755 or 775
- Files: 644 or 664
- Storage and bootstrap/cache directories need write permissions

## Troubleshooting

If you encounter issues:
1. Check that the `index.php` correctly references the application root
2. Ensure all required PHP extensions are available on the server
3. Make sure file permissions are set correctly
4. Check error logs in `storage/logs/`
