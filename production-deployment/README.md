# PTSP MTsN 2 KOTA MALANG - Production Deployment

This package contains the production-ready files for the PTSP (Pelayanan Terpadu Satu Pintu) application for MTsN 2 KOTA MALANG.

## Directory Structure

This package contains two main directories:
- `subdomain-root/` - Files that need to be placed in the subdomain's web-accessible root directory
- `application-root/` - Application files that should be placed OUTSIDE the web-accessible directory for security

## Installation Instructions

### 1. Upload Directories
- Upload the contents of `subdomain-root/` to your subdomain's web directory (e.g., public_html/subdomain/)
- Upload the entire `application-root/` directory to a non-web-accessible location (typically one directory level above your web root)

### 2. Set File Permissions
- Set permissions for the storage directory and its subdirectories to be writable (typically 755 or 775)
- Set permissions for the bootstrap/cache directory to be writable (755 or 775)

### 3. Configure Environment
- Edit the `.env` file in the application root directory to match your server settings
- Update database connection settings
- Update application URL

### 4. Install Dependencies
- Navigate to the application root directory via command line
- Run: `composer install --no-dev --optimize-autoloader`

### 5. Generate Application Key
- Run: `php artisan key:generate`

### 6. Run Database Migrations
- Run: `php artisan migrate --force`

### 7. Optimize Application
Run the following commands to optimize the application:
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

### 8. Test the Installation
Visit your subdomain to ensure the application is working properly.

## Security Notes

- The application root directory must remain outside the web-accessible directory
- Ensure that sensitive files like .env, artisan, and composer files are not publicly accessible
- Regular security updates should be applied
- Monitor access and error logs regularly

## Troubleshooting

If you encounter issues:
- Check that file permissions are set correctly
- Verify that required PHP extensions are installed on your server
- Review the Laravel logs in the storage directory
- Ensure the .htaccess files are properly configured