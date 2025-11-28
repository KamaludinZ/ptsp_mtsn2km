@echo off
echo Preparing production deployment without Vite...

echo Installing PHP dependencies...
composer install --optimize-autoloader --no-dev

echo Installing Node dependencies...
npm install --production

echo Clearing and caching configuration...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo Setting production environment...
echo APP_ENV=production >> .env
echo APP_DEBUG=false >> .env

echo Running database migrations...
php artisan migrate --force

echo Optimizing application...
php artisan optimize
php artisan config:cache
php artisan route:cache

echo Deployment completed successfully!
echo The application is now running without Vite in production mode.