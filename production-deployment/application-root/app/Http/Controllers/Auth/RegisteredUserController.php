<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\RegistrationCode;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use App\Services\WhatsAppService;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Determine if this is a civitas registration
        $isCivitas = $request->has('is_civitas') && $request->is_civitas == '1';

        // Base validation rules
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Additional validation for civitas
        if ($isCivitas) {
            $rules['registration_code'] = ['required', 'string', 'size:10', 'regex:/^[0-9]{10}$/'];
        } else {
            $rules['user_type'] = ['required', 'in:umum'];
        }

        $validated = $request->validate($rules);

        // Handle civitas registration
        $userType = 'umum';
        $registrationCode = null;

        if ($isCivitas) {
            // Verify registration code
            $regCode = RegistrationCode::where('code', $request->registration_code)
                ->active()
                ->first();

            if (!$regCode) {
                throw ValidationException::withMessages([
                    'registration_code' => 'Kode registrasi tidak valid atau sudah tidak aktif.',
                ]);
            }

            if (!$regCode->canBeUsed()) {
                throw ValidationException::withMessages([
                    'registration_code' => 'Kode registrasi sudah tidak dapat digunakan (sudah mencapai batas penggunaan atau kadaluarsa).',
                ]);
            }

            // Set user type from registration code
            $userType = $regCode->user_type;
            $registrationCode = $request->registration_code;
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $request->whatsapp_number,
            'password' => Hash::make($request->password),
            'user_type' => $userType,
            'registration_code' => $registrationCode,
            'is_active' => true,
        ]);

        // Increment registration code usage if civitas
        if ($isCivitas && isset($regCode)) {
            $regCode->incrementUsage();
        }

        // Assign role based on user type
        $user->assignRole($userType);

        // Send WhatsApp notification
        $whatsappService = new WhatsAppService();
        $message = "Halo {$user->name}! Registrasi akun Anda di PTSP MTsN 2 Kota Malang berhasil. Silakan cek email Anda untuk verifikasi akun.";
        $whatsappService->sendMessage($user->whatsapp_number, $message);

        // Fire registered event (will send verification email)
        event(new Registered($user));

        // Auto login but redirect to verification notice
        // (user needs to be logged in to access verification.notice route)
        Auth::login($user);

        // Redirect to verification notice
        return redirect()->route('verification.notice')
            ->with('status', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun.');
    }
}
