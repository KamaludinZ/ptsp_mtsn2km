<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ViteFallbackMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only process HTML responses
        if (!$response->headers->get('Content-Type') || !str_contains($response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        // Get the response content
        $content = $response->getContent();

        // Check if we're in production or if Vite is not running but we're in dev
        $shouldUseFallback = false;
        
        if (App::environment('local')) {
            // In local environment, check if Vite is running
            $viteRunning = $this->isViteRunning();
            $shouldUseFallback = !$viteRunning && config('vite.fallback_to_production', true);
        } else {
            // In production environment
            $shouldUseFallback = true;
        }

        if ($shouldUseFallback) {
            // Replace Vite client script that might cause errors
            $content = preg_replace(
                '/<script[^>]*src=["\']?http:\/\/localhost:5173\/@vite\/client["\']?[^>]*><\/script>/', 
                '', 
                $content
            );
            
            // Replace Vite asset links with production equivalents
            $content = $this->replaceViteAssetsWithProduction($content);
        }

        // Update response content
        $response->setContent($content);

        return $response;
    }

    /**
     * Check if Vite dev server is running
     */
    private function isViteRunning(): bool
    {
        $host = config('vite.dev_server_host', 'localhost');
        $port = config('vite.dev_server_port', 5173);

        try {
            $ch = curl_init("http://{$host}:{$port}/@vite/client");
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 2);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return in_array($retcode, [200, 404]); // 200 means server is running, 404 means it's running but endpoint doesn't exist
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Replace Vite development asset URLs with production equivalents
     */
    private function replaceViteAssetsWithProduction(string $content): string
    {
        // This is a simplified replacement - in real implementation, you'd read from manifest.json
        // and replace the URLs appropriately
        
        // First, we need to identify Vite asset references and replace them
        $content = preg_replace_callback(
            '/(href|src)=["\']?http:\/\/localhost:5173\/([^"\']*)["\']?/',
            function ($matches) {
                $attr = $matches[1];
                $assetPath = $matches[2];
                
                // Map development asset to production asset using manifest
                $productionAsset = $this->mapAssetToProduction($assetPath);
                
                return $attr . '="' . $productionAsset . '"';
            },
            $content
        );
        
        return $content;
    }

    /**
     * Map development asset path to production asset path using manifest
     */
    private function mapAssetToProduction(string $devAsset): string 
    {
        $manifestPath = public_path('build/manifest.json');

        if (!file_exists($manifestPath)) {
            // If manifest doesn't exist, return the original asset as fallback
            return asset($devAsset);
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Look for the asset in manifest (with and without 'resources/' prefix)
        $key = ltrim($devAsset, '/');
        if (isset($manifest[$key])) {
            return asset('build/' . $manifest[$key]['file']);
        }
        
        $keyWithResources = 'resources/' . $key;
        if (isset($manifest[$keyWithResources])) {
            return asset('build/' . $manifest[$keyWithResources]['file']);
        }
        
        // If not found in manifest, return as is but with asset() helper for production
        return asset($devAsset);
    }
}
