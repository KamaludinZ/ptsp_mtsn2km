@echo off
echo Building assets without Vite...

REM This script runs the Laravel Mix build process (alternative to Vite)
echo Installing Node dependencies...
npm install

echo Running Laravel Mix build...
npm run build

REM Alternative: Copy assets directly if you want to skip build process
REM This copies from resources to public/build manually
echo Copying assets to build directory...

REM Create build directory if not exists
if not exist "public\build" mkdir "public\build"
if not exist "public\build\assets" mkdir "public\build\assets"

REM Copy CSS files
xcopy /s /y "resources\css\*.css" "public\build\assets\"

REM Copy JS files
xcopy /s /y "resources\js\*.js" "public\build\assets\"

echo Build process completed.
echo All assets have been copied to public/build/assets/