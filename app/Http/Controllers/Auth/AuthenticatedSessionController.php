<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        // Generate captcha and store it in session
        $captcha = $this->generateCaptcha();
        session(['captcha_value' => $captcha]);
        
        return view('auth.login', compact('captcha'));
    }

    /**
     * Generate a random 6-digit captcha
     */
    private function generateCaptcha(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Refresh captcha and return as JSON
     */
    public function refreshCaptcha()
    {
        $captcha = $this->generateCaptcha();
        session(['captcha_value' => $captcha]);
        
        return response()->json(['captcha' => $captcha]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Staff holding several roles choose the active one first; the page they
        // were heading to stays as the intended URL for after they choose.
        if (\App\Support\ActiveRoles::startSession($request->user(), $request->session())) {
            return redirect()->to(\App\Filament\Pages\ChooseActiveRole::getUrl(panel: 'admin'));
        }

        // Land on the dashboard that belongs to the account's role
        return redirect()->intended(get_dashboard_route_for_user($request->user()));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/')->with('signed_out', true);
    }
}
