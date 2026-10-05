<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\CivitasRegistration;
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
        return view('auth.register', [
            'civitasTypes' => CivitasRegistration::USER_TYPES,
            'civitasOpen' => CivitasRegistration::code() !== null,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // E-mail addresses are stored in lower case (and unique regardless of case).
        $request->merge(['email' => mb_strtolower(trim((string) $request->input('email')))]);

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
            $rules['registration_code'] = ['required', 'string', 'max:64'];
            $rules['civitas_type'] = ['required', 'in:' . implode(',', array_keys(CivitasRegistration::USER_TYPES))];
        } else {
            $rules['user_type'] = ['required', 'in:umum'];
        }

        $request->validate($rules, [
            'civitas_type.required' => 'Pilih status Anda.',
            'civitas_type.in' => 'Status yang dipilih tidak valid.',
        ]);

        // Civitas: the shared code proves membership, the chosen status sets the account type
        $userType = 'umum';
        $registrationCode = null;

        if ($isCivitas) {
            if (! CivitasRegistration::matches($request->registration_code)) {
                throw ValidationException::withMessages([
                    'registration_code' => CivitasRegistration::code() === null
                        ? 'Pendaftaran civitas sedang ditutup. Hubungi admin madrasah.'
                        : 'Kode registrasi tidak valid.',
                ]);
            }

            $userType = $request->civitas_type;
            $registrationCode = trim($request->registration_code);
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
