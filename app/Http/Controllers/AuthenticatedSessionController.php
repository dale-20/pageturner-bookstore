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
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(); // validates credentials + rate-limits via LoginRequest

        $user = auth()->user();

        if ($user->two_factor_enabled) {
            // Log them back out, store pending user ID in session for the challenge
            auth()->logout();

            session([
                '2fa_user_id'  => $user->id,
                '2fa_type'     => $user->two_factor_type,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        // Mark 2FA as not required (user has no 2FA enabled)
        session(['two_factor_verified' => true]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

}