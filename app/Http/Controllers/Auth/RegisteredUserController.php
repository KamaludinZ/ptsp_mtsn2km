<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\FrontDeskService;
use App\Support\CivitasRegistration;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', [
            'civitasTypes' => CivitasRegistration::USER_TYPES,
            'civitasOpen' => CivitasRegistration::isOpen(),
            'civitasCodeRequired' => CivitasRegistration::codeRequired(),
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

        // Base validation rules. An e-mail already in use is refused here, with
        // the other errors, unless it belongs to an unclaimed counter account,
        // which the registration then links instead of duplicating.
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', function (string $attribute, $value, \Closure $fail) {
                if (FrontDeskService::emailTaken((string) $value)) {
                    $fail(FrontDeskService::EMAIL_TAKEN);
                }
            }],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        // Additional validation for civitas
        if ($isCivitas) {
            $rules['registration_code'] = [CivitasRegistration::codeRequired() ? 'required' : 'nullable', 'string', 'max:64'];
            $rules['civitas_type'] = ['required', 'in:' . implode(',', array_keys(CivitasRegistration::USER_TYPES))];
        } else {
            $rules['user_type'] = ['required', 'in:umum'];
        }

        $request->validate($rules, [
            'civitas_type.required' => 'Pilih status Anda.',
            'civitas_type.in' => 'Status yang dipilih tidak valid.',
        ]);

        // Civitas: the admin opens the form; when required, the shared code proves
        // membership. The chosen status sets the account type.
        $userType = 'umum';
        $registrationCode = null;

        if ($isCivitas) {
            if (! CivitasRegistration::isOpen()) {
                throw ValidationException::withMessages([
                    CivitasRegistration::codeRequired() ? 'registration_code' : 'civitas_type' => 'Pendaftaran civitas sedang ditutup. Hubungi admin madrasah.',
                ]);
            }

            if (CivitasRegistration::codeRequired()) {
                if (! CivitasRegistration::matches($request->registration_code)) {
                    throw ValidationException::withMessages(['registration_code' => 'Kode registrasi tidak valid.']);
                }

                $registrationCode = trim($request->registration_code);
            }

            $userType = $request->civitas_type;
        }

        // Penautan akun: pemohon yang sudah dicatat di loket (email atau WhatsApp
        // sama, belum pernah dipakai) memakai akun itu beserta riwayat
        // permohonannya, bukan akun ganda. Satu layanan dengan loket.
        $link = app(FrontDeskService::class)->registerPortalApplicant([
            'name' => $request->name,
            'email' => $request->email,
            'whatsapp_number' => $request->whatsapp_number,
            'password' => Hash::make($request->password),
        ], $userType, $registrationCode);
        $user = $link->user;

        // The WhatsApp welcome goes out only once the email is verified
        // (AppServiceProvider, Verified event), so a stranger's sign-up form
        // cannot make the school's number message any phone.

        // Fire registered event (will send verification email)
        event(new Registered($user));

        // Auto login but redirect to verification notice
        // (user needs to be logged in to access verification.notice route)
        Auth::login($user);

        // Redirect to verification notice
        // Hasil penautan ditampilkan tanpa rincian riwayat: email belum terverifikasi.
        return redirect()->route('verification.notice')
            ->with('status', 'Registrasi berhasil! Silakan cek email Anda untuk verifikasi akun.')
            ->with('account_linked', ! $link->isNew());
    }
}
