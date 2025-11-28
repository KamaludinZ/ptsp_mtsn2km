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
            $envData = [];

            $settingsToUpdate = [];
            foreach ($data as $key => $value) {
                if ($value === null && !$request->hasFile($key)) {
                    continue;
                }

                if ($request->hasFile($key)) {
                    // Handle file uploads
                    $file = $request->file($key);
                    $setting = AppSetting::where('key', $key)->first();
                    if ($setting && $setting->value && Storage::disk('public')->exists($setting->value)) {
                        Storage::disk('public')->delete($setting->value);
                    }
                    $path = $file->store('settings', 'public');
                    $value = $path;
                }

                // Separate .env and database settings
                if (in_array($key, ['whatsapp_api_token', 'mail_password'])) {
                    $envKey = strtoupper($key);
                    $envData[$envKey] = $value;
                } else if (in_array($key, ['whatsapp_api_url', 'whatsapp_sender_id', 'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'mail_from_name'])) {
                    $envKey = strtoupper($key);
                    $envData[$envKey] = $value;
                    $settingsToUpdate[$key] = $value;
                } else {
                    $settingsToUpdate[$key] = $value;
                }
            }

            // Update database settings
            foreach ($settingsToUpdate as $key => $value) {
                AppSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            }

            // Update .env file
            if (!empty($envData)) {
                $this->updateEnv($envData);
            }

            Cache::flush();

            return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Helper to update .env file
     */
    private function updateEnv(array $data)
    {
        $envFilePath = app()->environmentFilePath();
        $content = file_get_contents($envFilePath);

        foreach ($data as $key => $value) {
            $key = strtoupper($key);
            $value = '"' . $value . '"'; // Add quotes to handle spaces
            if (strpos($content, $key . '=') !== false) {
                $content = preg_replace('/^' . $key . '=.*/m', $key . '=' . $value, $content);
            } else {
                $content .= "\n" . $key . '=' . $value;
            }
        }

        file_put_contents($envFilePath, $content);
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