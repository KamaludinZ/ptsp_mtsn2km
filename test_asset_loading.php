<?php

/**
 * Test Asset Loading
 * Verifikasi bahwa semua asset bisa dimuat dengan benar
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\File;

echo "=== ASSET LOADING VERIFICATION ===\n\n";

// Check .env configuration
echo "📋 Configuration:\n";
echo "  ASSET_MODE: " . config('assets.mode') . "\n";
echo "  CHECK_VITE_SERVER: " . (config('assets.check_vite_server') ? 'true' : 'false') . "\n";
echo "  APP_ENV: " . config('app.env') . "\n";
echo "\n";

// Check critical assets
$criticalAssets = [
    'css' => [
        'resources/css/app.css',
        'resources/css/bootstrap-custom.css',
        'resources/css/fontawesome.css',
    ],
    'js' => [
        'resources/js/app.js',
        'resources/js/bootstrap-bundle.js',
    ]
];

echo "🔍 Checking Critical Assets:\n\n";

$allOk = true;

foreach ($criticalAssets as $type => $assets) {
    echo strtoupper($type) . " Files:\n";

    foreach ($assets as $asset) {
        // Get mapped path from config
        $productionAssets = config("assets.production_assets.{$type}", []);
        $mappedPath = $productionAssets[$asset] ?? null;

        if (!$mappedPath) {
            echo "  ❌ {$asset}\n";
            echo "     → No mapping in config/assets.php\n";
            $allOk = false;
            continue;
        }

        // Check if file exists
        $fullPath = public_path($mappedPath);
        if (File::exists($fullPath)) {
            $size = File::size($fullPath);
            $sizeKb = round($size / 1024, 2);
            echo "  ✓ {$asset}\n";
            echo "     → {$mappedPath} ({$sizeKb} KB)\n";
        } else {
            echo "  ❌ {$asset}\n";
            echo "     → Mapped to: {$mappedPath}\n";
            echo "     → File not found!\n";
            $allOk = false;
        }
    }
    echo "\n";
}

// Check manifest.json
echo "📄 Checking Vite Manifest:\n";
$manifestPath = public_path('build/manifest.json');

if (File::exists($manifestPath)) {
    echo "  ✓ public/build/manifest.json exists\n";

    $manifest = json_decode(File::get($manifestPath), true);
    if (is_array($manifest)) {
        echo "  ✓ Manifest is valid JSON\n";
        echo "  → Total entries: " . count($manifest) . "\n";

        // Check critical entries
        $missingEntries = [];
        foreach ($criticalAssets['css'] as $asset) {
            if (!isset($manifest[$asset])) {
                $missingEntries[] = $asset;
            }
        }

        if (empty($missingEntries)) {
            echo "  ✓ All critical CSS entries found in manifest\n";
        } else {
            echo "  ⚠ Missing entries in manifest:\n";
            foreach ($missingEntries as $entry) {
                echo "    - {$entry}\n";
            }
        }
    } else {
        echo "  ❌ Manifest is not valid JSON\n";
        $allOk = false;
    }
} else {
    echo "  ❌ Manifest not found\n";
    echo "     → Run: npm run build\n";
    $allOk = false;
}

echo "\n";

// Test AssetHelper
echo "🧪 Testing AssetHelper:\n";

try {
    $testAssets = [
        'resources/css/app.css',
        'resources/css/bootstrap-custom.css',
        'resources/css/fontawesome.css',
    ];

    foreach ($testAssets as $asset) {
        $html = \App\Helpers\AssetHelper::css($asset);

        // Extract href from HTML
        preg_match('/href="([^"]+)"/', $html, $matches);
        $url = $matches[1] ?? '';

        if (!empty($url)) {
            // Parse URL to get path
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] ?? '';
            $path = ltrim($path, '/');

            // Check if file exists
            if (File::exists(public_path($path))) {
                echo "  ✓ {$asset}\n";
                echo "     → {$url}\n";
            } else {
                echo "  ❌ {$asset}\n";
                echo "     → Generated URL: {$url}\n";
                echo "     → File not found at: {$path}\n";
                $allOk = false;
            }
        } else {
            echo "  ❌ {$asset}\n";
            echo "     → AssetHelper returned empty URL\n";
            $allOk = false;
        }
    }
} catch (\Exception $e) {
    echo "  ❌ AssetHelper error: " . $e->getMessage() . "\n";
    $allOk = false;
}

echo "\n";
echo "=== SUMMARY ===\n";

if ($allOk) {
    echo "✅ All checks passed!\n";
    echo "\nYou can now:\n";
    echo "  1. Open browser and go to login page\n";
    echo "  2. Open Developer Tools (F12)\n";
    echo "  3. Check Console tab - should have no 404 errors\n";
    echo "  4. Check Network tab - all assets should load with 200 status\n";
} else {
    echo "❌ Some checks failed!\n";
    echo "\nNext steps:\n";
    echo "  1. Run: npm run build\n";
    echo "  2. Check config/assets.php mappings\n";
    echo "  3. Run: php artisan config:clear\n";
    echo "  4. Run this script again\n";
}

echo "\n=== END ===\n";
