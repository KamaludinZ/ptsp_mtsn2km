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
        $mode = config('assets.mode', 'vite');
        $checkVite = config('assets.check_vite_server', true);

        // Determine if we should use Vite
        $shouldUseVite = false;
        if ($mode === 'vite' && config('app.env') === 'local') {
            $shouldUseVite = $checkVite ? self::isViteRunning() : true;
        }

        if ($shouldUseVite) {
            // For Vite in dev, we return a placeholder since we can't return @vite from helper
            // The actual @vite should be used directly in blade files
            return '<!-- VITE_CSS_PLACEHOLDER:' . $path . ' -->';
        }

        // Use compiled assets
        $url = self::asset($path);
        return '<link rel="stylesheet" href="' . $url . '">';
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
        $mode = config('assets.mode', 'vite');
        $checkVite = config('assets.check_vite_server', true);

        // Determine if we should use Vite
        $shouldUseVite = false;
        if ($mode === 'vite' && config('app.env') === 'local') {
            $shouldUseVite = $checkVite ? self::isViteRunning() : true;
        }

        if ($shouldUseVite) {
            // For Vite in dev, we return a placeholder since we can't return @vite from helper
            return '<!-- VITE_JS_PLACEHOLDER:' . $path . ' -->';
        }

        // Use compiled assets
        $url = self::asset($path);
        $deferAttr = $defer ? ' defer' : '';
        return '<script src="' . $url . '"' . $deferAttr . '></script>';
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
            return $key . '="' . e($value, false) . '"';
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
        $manifestPath = public_path('build/manifest.json');

        // First, check if there's a configured fallback
        $fallbackPath = config("assets.production_assets." . pathinfo($path, PATHINFO_EXTENSION) . "." . $path);
        if ($fallbackPath) {
            return asset($fallbackPath);
        }

        if (!File::exists($manifestPath)) {
            // Fallback: try to find the most recent compiled asset with matching name
            return self::findAssetFromBuildDir($path);
        }

        $manifest = json_decode(File::get($manifestPath), true);

        // Remove 'resources/' prefix if exists
        $key = str_replace('resources/', '', $path);

        if (isset($manifest[$path])) {
            return asset('build/' . $manifest[$path]['file']);
        } elseif (isset($manifest[$key])) {
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
    private static function isViteRunning()
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

            return $retcode === 200;
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
