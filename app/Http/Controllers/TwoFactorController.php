<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorOtpMail;
use App\Notifications\TwoFactorDisabledNotification;
use App\Notifications\TwoFactorEnabledNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    // --- PROFILE: Show 2FA settings ---
    public function show()
    {
        return view('profile.two-factor');
    }

    // --- TOTP: Generate secret & QR code ---
    public function setupTotp(Request $request)
    {
        $user   = $request->user();
        $secret = $this->google2fa->generateSecretKey();

        session(['2fa_totp_secret' => $secret]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('profile.two-factor-setup-totp', [
            'qrCodeUrl' => $qrCodeUrl,
            'secret'    => $secret,
        ]);
    }

    // --- TOTP: Confirm and enable ---
    public function confirmTotp(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $secret = session('2fa_totp_secret');
        $valid  = $this->google2fa->verifyKey($secret, $request->code);

        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }

        $user = $request->user();
        $user->update([
            'two_factor_enabled'        => true,
            'two_factor_type'           => 'totp',
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
            'two_factor_confirmed_at'   => now(),
        ]);

        session()->forget('2fa_totp_secret');

        // Notify the user that 2FA was enabled
        $user->notify(new TwoFactorEnabledNotification('totp'));

        return redirect()->route('profile.two-factor.recovery-codes')
            ->with('status', '2FA enabled successfully!');
    }

    // --- Email OTP: Enable ---
    public function enableEmail(Request $request)
    {
        $user = $request->user();
        $user->update([
            'two_factor_enabled'        => true,
            'two_factor_type'           => 'email',
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
            'two_factor_confirmed_at'   => now(),
        ]);

        // Notify the user that 2FA was enabled
        $user->notify(new TwoFactorEnabledNotification('email'));

        return redirect()->route('profile.edit')
            ->with('status', 'Email 2FA enabled successfully!');
    }

    // --- Disable 2FA ---
    public function disable(Request $request)
    {
        $request->validate(['password' => 'required|current_password']);

        $user = $request->user();
        $user->update([
            'two_factor_enabled'        => false,
            'two_factor_secret'         => null,
            'two_factor_type'           => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);

        // Notify the user that 2FA was disabled
        $user->notify(new TwoFactorDisabledNotification());

        return back()->with('status', '2FA has been disabled.');
    }

    // --- Show recovery codes ---
    public function recoveryCodes(Request $request)
    {
        return view('profile.two-factor-recovery-codes', [
            'codes' => $request->user()->two_factor_recovery_codes,
        ]);
    }

    // --- LOGIN: Show challenge ---
    public function challenge()
    {
        if (!session('2fa_user_id')) {
            return redirect()->route('login');
        }

        $type = session('2fa_type');

        if ($type === 'email') {
            $this->sendEmailOtp();
        }

        return view('auth.two-factor-challenge', ['type' => $type]);
    }

    // --- LOGIN: Verify challenge ---
    public function verifyChallenge(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $userId = session('2fa_user_id');
        $user   = \App\Models\User::findOrFail($userId);
        $code   = trim($request->code);

        // Check recovery codes first
        if ($this->verifyRecoveryCode($user, $code)) {
            return $this->completeTwoFactorLogin($user);
        }

        if ($user->two_factor_type === 'totp') {
            $secret = decrypt($user->two_factor_secret);
            $valid  = $this->google2fa->verifyKey($secret, $code);
        } else {
            $valid = cache("2fa_otp_{$userId}") === $code
                && now()->lt(cache("2fa_otp_{$userId}_expires"));
        }

        if (!$valid) {
            return back()->withErrors(['code' => 'The code you entered is incorrect.']);
        }

        return $this->completeTwoFactorLogin($user);
    }

    // --- Resend email OTP ---
    public function resendOtp()
    {
        $this->sendEmailOtp();
        return back()->with('status', 'A new code has been sent to your email.');
    }

    // --- Helpers ---
    private function sendEmailOtp(): void
    {
        $userId = session('2fa_user_id');
        $user   = \App\Models\User::findOrFail($userId);
        $otp    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        cache(["2fa_otp_{$userId}"         => $otp],             now()->addMinutes(10));
        cache(["2fa_otp_{$userId}_expires" => now()->addMinutes(10)], now()->addMinutes(10));

        Mail::to($user->email)->send(new TwoFactorOtpMail($otp));
    }

    private function verifyRecoveryCode($user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $key   = array_search($code, $codes);

        if ($key !== false) {
            unset($codes[$key]);
            $user->update(['two_factor_recovery_codes' => array_values($codes)]);
            return true;
        }

        return false;
    }

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn() => Str::random(5) . '-' . Str::random(5))
            ->toArray();
    }

    private function completeTwoFactorLogin($user)
    {
        // Clear 2FA session data
        session()->forget(['2fa_user_id', '2fa_type']);

        // Login the user
        auth()->login($user);

        // Regenerate session AFTER login to prevent session fixation
        session()->regenerate();

        // Redirect based on role so middleware doesn't bounce them
        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}