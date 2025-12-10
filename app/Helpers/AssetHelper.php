<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;

class AssetHelper
{
    /**
     * Get asset URL based on configuration
     *
     * @param string $path Path to asset (e.g., 'resources/css/app.css')
     * @return string Full URL to asset
     */
    public static function asset($path)
    {
        try {
            $mode = config('assets.mode', 'vite');
            $checkVite = config('assets.check_vite_server', true);

            // Determine if we should use Vite
            $shouldUseVite = false;
            if ($mode === 'vite' && config('app.env') === 'local') {
                $shouldUseVite = $checkVite ? self::isViteRunning() : true;
            }

            if ($shouldUseVite) {
                return self::viteAsset($path);
            }

            // In production mode or if Vite is not running, use compiled assets
            return self::manifestAsset($path);
        } catch (\Exception $e) {
            // Log the error but don't break the page
            \Log::error('AssetHelper::asset error', [
                'path' => $path,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            // Return a safe fallback - just use Laravel's asset() helper
            return asset($path);
        }
    }

    /**
     * Get multiple assets
     *
     * @param array $paths Array of asset paths
     * @return array Array of asset URLs
     */
    public static function assets($paths)
    {
        return array_map(function($path) {
            return self::asset($path);
        }, $paths);
    }

    /**
     * Load CSS asset based on configuration
     *
     * @param string $path Path to CSS file
     * @return string HTML link tag
     */
    public static function css($path)
    {
        try {
            // Validate input parameters
            if (!is_string($path) || empty($path)) {
                \Log::warning('AssetHelper::css called with invalid path', [
                    'path' => $path,
                    'type' => gettype($path)
                ]);
                return '<link rel="stylesheet" href="">'; // Return empty href as safe fallback
            }

            $mode = config('assets.mode', 'vite');
            $checkVite = config('assets.check_vite_server', true);

            // Determine if we should use Vite
            $shouldUseVite = false;
            if ($mode === 'vite' && config('app.env') === 'local') {
                $shouldUseVite = $checkVite ? self::isViteRunning() : true;
            }

            // Only allow valid asset paths to prevent injection
            if (!is_string($path) || empty($path) || !preg_match('/^[a-zA-Z0-9\/_.\-@]+$/', $path)) {
                return '<link rel="stylesheet" href="">';
            }

            // Validate path to ensure it's actually an asset file
            if (!preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $path)) {
                return '<link rel="stylesheet" href="">';
            }

            // Use manifestAsset to get the correct path
            $url = self::manifestAsset($path);
            return '<link rel="stylesheet" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">';
        } catch (\Exception $e) {
            // In case of any error, return a safe empty link tag to avoid breaking page
            return '<link rel="stylesheet" href="">';
        }
    }

    /**
     * Load JS asset based on configuration
     *
     * @param string $path Path to JS file
     * @param bool $defer Add defer attribute
     * @return string HTML script tag
     */
    public static function js($path, $defer = true)
    {
        try {
            // Only allow valid asset paths to prevent injection
            if (!is_string($path) || empty($path) || !preg_match('/^[a-zA-Z0-9\/_.\-@]+$/', $path)) {
                return '<script></script>';
            }

            // Validate path to ensure it's actually an asset file
            if (!preg_match('/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i', $path)) {
                return '<script></script>';
            }

            // Use manifestAsset to get the correct path
            $url = self::manifestAsset($path);

            $deferAttr = $defer ? ' defer' : '';
            return '<script src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . htmlspecialchars($deferAttr, ENT_QUOTES, 'UTF-8') . '></script>';
        } catch (\Exception $e) {
            // In case of any error, return a safe empty script tag to avoid breaking page
            return '<script></script>';
        }
    }

    /**
     * Generate preload links for assets.
     *
     * @param array $paths
     * @return string
     */
    public static function preload($paths)
    {
        $links = [];
        foreach ($paths as $path) {
            $url = self::asset($path);
            $as = self::getAssetType($path);
            if ($as) {
                $attributes = [
                    'rel' => 'preload',
                    'href' => $url,
                    'as' => $as,
                ];

                if ($as === 'font') {
                    $fontMimeType = self::getFontMimeType($path);
                    if ($fontMimeType) {
                        $attributes['type'] = $fontMimeType;
                    }
                    $attributes['crossorigin'] = 'anonymous';
                }

                $links[] = '<link' . self::htmlAttributes($attributes) . '>';
            }
        }
        return implode("\n", $links);
    }

    /**
     * Get the asset type for the 'as' attribute.
     *
     * @param string $path
     * @return string|null
     */
    private static function getAssetType($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'css':
                return 'style';
            case 'js':
                return 'script';
            case 'woff':
            case 'woff2':
            case 'ttf':
            case 'otf':
            case 'eot':
                return 'font';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'svg':
            case 'webp':
                return 'image';
            default:
                return null;
        }
    }

    /**
     * Get the MIME type for a font file.
     *
     * @param string $path
     * @return string|null
     */
    private static function getFontMimeType($path)
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        switch ($extension) {
            case 'woff':
                return 'font/woff';
            case 'woff2':
                return 'font/woff2';
            case 'ttf':
                return 'font/ttf';
            case 'otf':
                return 'font/otf';
            case 'eot':
                return 'application/vnd.ms-fontobject';
            default:
                return null;
        }
    }

    /**
     * Build an HTML attribute string from an array.
     *
     * @param  array  $attributes
     * @return string
     */
    private static function htmlAttributes($attributes)
    {
        $html = [];
        foreach ((array) $attributes as $key => $value) {
            $element = self::attributeElement($key, $value);
            if (! is_null($element)) {
                $html[] = $element;
            }
        }
        return count($html) > 0 ? ' ' . implode(' ', $html) : '';
    }

    /**
     * Build a single attribute element.
     *
     * @param  string  $key
     * @param  string  $value
     * @return string
     */
    private static function attributeElement($key, $value)
    {
        if (is_numeric($key)) {
            return $value;
        }
        if (is_bool($value) && $key !== 'value') {
            return $value ? $key : '';
        }
        if ($value !== null) {
            return $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }
        return null;
    }

    /**
     * Get asset from Vite manifest
     *
     * @param string $path Original asset path
     * @return string URL to compiled asset
     */
    private static function manifestAsset($path)
    {
        // Handle vendor/package assets - return them as-is (they manage their own compilation)
        if (str_starts_with($path, 'vendor/') || str_starts_with($path, '/vendor/')) {
            return asset($path);
        }

        // Handle Filament assets - they use their own build system
        if (str_contains($path, '/filament/') || str_contains($path, 'filament')) {
            return asset($path);
        }

        // Handle critical CSS files that contain Font Awesome and other icons
        if ($path === 'resources/css/vendor.css') {
            // Ensure vendor CSS (with Font Awesome) is always properly loaded
            $buildDir = public_path('build/assets');
            if (File::exists($buildDir)) {
                $files = File::glob($buildDir . '/vendor-*.css');
                if (!empty($files)) {
                    // Return the first vendor CSS file found
                    $filename = basename($files[0]);
                    return asset('build/assets/' . $filename);
                }
            }
        }

        // Handle Font Awesome CSS specifically
        if ($path === 'resources/css/fontawesome.css') {
            $buildDir = public_path('build/assets');
            if (File::exists($buildDir)) {
                $files = File::glob($buildDir . '/fontawesome-*.css');
                if (!empty($files)) {
                    // Return the first Font Awesome CSS file found
                    $filename = basename($files[0]);
                    return asset('build/assets/' . $filename);
                }
            }
        }

        // Handle specific CSS files that often cause issues in production
        if ($path === 'resources/css/public-layout.css' || $path === 'resources/css/loading.css') {
            $filename = pathinfo($path, PATHINFO_BASENAME);
            $staticPath = 'css/' . $filename;
            if (File::exists(public_path($staticPath))) {
                return asset($staticPath);
            }
        }

        // Handle specific JS files that should use compiled versions (no ES6 modules)
        if ($path === 'resources/js/app.js') {
            $compiledPath = 'js/app-compiled.js';
            if (File::exists(public_path($compiledPath))) {
                return asset($compiledPath);
            }
        }
        if ($path === 'resources/js/bootstrap-bundle.js') {
            $compiledPath = 'js/bootstrap-bundle-compiled.js';
            if (File::exists(public_path($compiledPath))) {
                return asset($compiledPath);
            }
        }
        if ($path === 'resources/js/chart-bundle.js') {
            $compiledPath = 'js/chart-bundle-compiled.js';
            if (File::exists(public_path($compiledPath))) {
                return asset($compiledPath);
            }
        }
        if ($path === 'resources/js/accessibility.js') {
            $compiledPath = 'js/accessibility-compiled.js';
            if (File::exists(public_path($compiledPath))) {
                return asset($compiledPath);
            }
        }

        $manifestPath = public_path('build/manifest.json');

        // First, check if there's a configured fallback
        if (!empty($path) && is_string($path)) {
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $fallbackPath = config("assets.production_assets.{$extension}.{$path}");
            if ($fallbackPath) {
                return asset($fallbackPath);
            }
        }

        if (!File::exists($manifestPath)) {
            // Fallback: try to find the most recent compiled asset with matching name
            return self::findAssetFromBuildDir($path);
        }

        $manifest = json_decode(File::get($manifestPath), true);

        // Check if manifest is valid
        if (!is_array($manifest)) {
            \Log::warning('Invalid manifest.json', ['path' => $manifestPath]);
            return self::findAssetFromBuildDir($path);
        }

        // Remove 'resources/' prefix if exists
        $key = str_replace('resources/', '', $path);

        if (isset($manifest[$path]) && is_array($manifest[$path]) && isset($manifest[$path]['file'])) {
            return asset('build/' . $manifest[$path]['file']);
        } elseif (isset($manifest[$key]) && is_array($manifest[$key]) && isset($manifest[$key]['file'])) {
            return asset('build/' . $manifest[$key]['file']);
        }

        // Fallback: try to find the most recent compiled asset with matching name
        return self::findAssetFromBuildDir($path);
    }

    /**
     * Find asset from build directory based on original file name
     *
     * @param string $originalPath Original asset path
     * @return string URL to matching compiled asset
     */
    private static function findAssetFromBuildDir($originalPath)
    {
        // Extract the base filename from the path (e.g., 'app.css' from 'resources/css/app.css')
        $filename = pathinfo($originalPath, PATHINFO_BASENAME);
        $ext = pathinfo($originalPath, PATHINFO_EXTENSION);
        $basename = pathinfo($originalPath, PATHINFO_FILENAME);

        // Search for files in build directory that match the pattern
        $buildDir = public_path('build/assets');
        if (!File::exists($buildDir)) {
            // Fallback: return original path
            return asset($originalPath);
        }

        $files = File::glob($buildDir . '/*.' . $ext);

        // Ensure $files is an array
        if (!is_array($files)) {
            $files = [];
        }

        // Look for the most appropriate file
        foreach ($files as $file) {
            $fileBasename = pathinfo($file, PATHINFO_FILENAME);
            if (stripos($fileBasename, $basename) !== false) {
                $relativePath = 'build/assets/' . basename($file);
                return asset($relativePath);
            }
        }

        // If no exact match, look for any file that starts with the basename
        foreach ($files as $file) {
            $fileBasename = pathinfo($file, PATHINFO_FILENAME);
            if (preg_match('/^' . preg_quote($basename, '/') . '-[a-zA-Z0-9]+/', $fileBasename)) {
                $relativePath = 'build/assets/' . basename($file);
                return asset($relativePath);
            }
        }

        // Fallback: return original path
        return asset($originalPath);
    }

    /**
     * Get asset from Vite dev server
     *
     * @param string $path Original asset path
     * @return string URL to Vite dev server
     */
    private static function viteAsset($path)
    {
        $devServerUrl = 'http://localhost:5173';

        // For Vite dev server, prepend with @ symbol
        return $devServerUrl . '/' . $path;
    }

    /**
     * Check if Vite dev server is running
     *
     * @return bool
     */
    public static function isViteRunning()
    {
        // Check if we're in production environment
        if (config('app.env') !== 'local') {
            return false; // Never use Vite in production
        }

        try {
            $ch = curl_init('http://localhost:5173');
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // 404 also means Vite server is running (just no route at root)
            return in_array($retcode, [200, 404]);
        } catch (\Exception $e) {
            // Additional check: try to ping the vite client endpoint
            try {
                $ch = curl_init('http://localhost:5173/@vite/client');
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_exec($ch);
                $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                return in_array($retcode, [200, 404]); // 404 means server is running but endpoint doesn't exist yet
            } catch (\Exception $e2) {
                return false;
            }
        }
    }
}