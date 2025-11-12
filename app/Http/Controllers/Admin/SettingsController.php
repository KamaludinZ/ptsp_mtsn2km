<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Display settings page
     */
    public function index()
    {
        $settings = AppSetting::all()->groupBy('category');

        // Available DaisyUI themes (32 official themes)
        $themes = [
            'light', 'dark', 'cupcake', 'bumblebee', 'emerald', 'corporate',
            'synthwave', 'retro', 'cyberpunk', 'valentine', 'halloween', 'garden',
            'forest', 'aqua', 'lofi', 'pastel', 'fantasy', 'wireframe', 'black',
            'luxury', 'dracula', 'cmyk', 'autumn', 'business', 'acid', 'lemonade',
            'night', 'coffee', 'winter', 'dim', 'nord', 'sunset'
        ];

        return view('admin.settings.index', compact('settings', 'themes'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        try {
            $data = $request->except(['_token', '_method']);

            foreach ($data as $key => $value) {
                // Skip null values
                if ($value === null) {
                    continue;
                }

                // Handle file uploads
                if ($request->hasFile($key)) {
                    $file = $request->file($key);
                    $setting = AppSetting::where('key', $key)->first();

                    // Delete old file if exists
                    if ($setting && $setting->value && Storage::disk('public')->exists($setting->value)) {
                        Storage::disk('public')->delete($setting->value);
                    }

                    // Store new file
                    if ($key === 'app_favicon') {
                        $path = $file->storeAs('settings', 'favicon.' . $file->getClientOriginalExtension(), 'public');
                    } elseif ($key === 'app_logo') {
                        $path = $file->storeAs('settings', 'logo.' . $file->getClientOriginalExtension(), 'public');
                    } else {
                        $path = $file->store('settings', 'public');
                    }

                    $value = $path;
                }

                // Update or create setting
                AppSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }

            // Clear cache
            Cache::flush();

            return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Get setting by key (API endpoint)
     */
    public function getSetting($key)
    {
        $setting = AppSetting::where('key', $key)->first();

        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        return response()->json($setting);
    }

    /**
     * Update single setting (API endpoint)
     */
    public function updateSetting(Request $request, $key)
    {
        try {
            $value = $request->input('value');

            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );

            // Clear cache
            Cache::forget("app_setting_{$key}");

            return response()->json(['success' => true, 'message' => 'Setting updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset settings to default
     */
    public function reset()
    {
        try {
            // Run the seeder to reset to defaults
            \Artisan::call('db:seed', ['--class' => 'AppSettingsSeeder', '--force' => true]);

            Cache::flush();

            return redirect()->back()->with('success', 'Pengaturan berhasil direset ke default!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mereset pengaturan: ' . $e->getMessage());
        }
    }
}