{{--
    resources/views/auth/two-factor-challenge.blade.php
    Shown during login when 2FA is enabled.
    Supports both TOTP (authenticator app) and Email OTP.
--}}

<x-guest-layout>

    {{-- Header --}}
    <div class="mb-6 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl mb-4"
             style="background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 10px 30px rgba(220,38,38,0.3);">
            @if($type === 'totp')
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                </svg>
            @endif
        </div>
        <h2 class="text-xl font-bold text-gray-900">Two-Factor Verification</h2>
        @if($type === 'totp')
            <p class="text-sm text-gray-500 mt-1">Enter the 6-digit code from your authenticator app.</p>
        @else
            <p class="text-sm text-gray-500 mt-1">A 6-digit code was sent to your email address.</p>
        @endif
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="mb-4 p-3 rounded-lg text-sm flex items-center gap-2"
             style="background: rgba(220,38,38,0.08); border: 1.5px solid rgba(220,38,38,0.25); color: #dc2626;">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('status'))
        <div class="mb-4 p-3 rounded-lg text-sm flex items-center gap-2"
             style="background: rgba(22,163,74,0.08); border: 1.5px solid rgba(22,163,74,0.25); color: #16a34a;">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('two-factor.verify') }}" id="challengeForm">
        @csrf

        {{-- OTP digit inputs --}}
        <div class="mb-5" id="otpSection">
            <label class="block text-xs font-semibold text-gray-400 text-center tracking-widest mb-3">
                VERIFICATION CODE
            </label>
            <div class="flex justify-center gap-2" id="otpInputs">
                @for($i = 0; $i < 6; $i++)
                    <input type="text"
                           class="otp-digit text-center font-bold text-2xl border-2 border-gray-200 rounded-xl focus:outline-none"
                           style="width: 48px; height: 56px; transition: border-color 0.2s, box-shadow 0.2s;"
                           maxlength="1"
                           inputmode="numeric"
                           pattern="[0-9]">
                @endfor
            </div>
            <input type="hidden" name="code" id="otpHidden">
        </div>

        {{-- Recovery code input (hidden by default) --}}
        <div class="mb-5 hidden" id="recoverySection">
            <label class="block text-xs font-semibold text-gray-400 tracking-widest mb-2">RECOVERY CODE</label>
            <input type="text" id="recoveryInput"
                   class="block w-full border border-gray-300 rounded-lg px-4 py-3 text-sm font-mono tracking-widest focus:outline-none focus:ring-2 focus:border-red-500"
                   placeholder="xxxxx-xxxxx">
        </div>

        {{-- Submit --}}
        <button type="submit" id="verifyBtn"
                class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-lg text-white font-semibold text-sm transition-all"
                style="background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 4px 14px rgba(220,38,38,0.35);">
            <span id="btnText" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Verify Identity
            </span>
            <span id="btnLoading" class="hidden items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Verifying...
            </span>
        </button>
    </form>

    {{-- Resend button (email only) --}}
    @if($type === 'email')
        <form method="POST" action="{{ route('two-factor.resend') }}" class="mt-3">
            @csrf
            <button type="submit"
                    class="w-full py-3 px-4 rounded-lg border-2 border-gray-200 text-sm font-semibold text-gray-500 hover:bg-gray-50 transition">
                ↺ Resend Code to Email
            </button>
        </form>
    @endif

    {{-- Toggle recovery code --}}
    <div class="text-center mt-4">
        <button type="button" id="toggleRecovery"
                class="text-sm text-gray-400 hover:text-gray-600 underline underline-offset-2 transition">
            Use a recovery code instead
        </button>
    </div>

    {{-- Back to login --}}
    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:underline">
            ← Sign in with a different account
        </a>
    </div>

    <style>
        .otp-digit:focus {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15) !important;
        }
        .otp-digit.filled {
            border-color: #dc2626;
            background: rgba(220, 38, 38, 0.04);
            color: #dc2626;
        }
    </style>

    <script>
        const digits     = document.querySelectorAll('.otp-digit');
        const hidden     = document.getElementById('otpHidden');
        const otpSection = document.getElementById('otpSection');
        const recovSec   = document.getElementById('recoverySection');
        const recovInput = document.getElementById('recoveryInput');
        let usingRecovery = false;

        if (digits.length) digits[0].focus();

        digits.forEach((input, idx) => {
            input.addEventListener('input', e => {
                const val = e.target.value.replace(/\D/g, '');
                e.target.value = val;
                val ? input.classList.add('filled') : input.classList.remove('filled');
                if (val && idx < 5) digits[idx + 1].focus();
                syncHidden();
                if (val && idx === 5) syncAndSubmit();
            });

            input.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !input.value && idx > 0) {
                    digits[idx - 1].value = '';
                    digits[idx - 1].classList.remove('filled');
                    digits[idx - 1].focus();
                    syncHidden();
                }
            });

            input.addEventListener('paste', e => {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData)
                    .getData('text').replace(/\D/g, '').slice(0, 6);
                pasted.split('').forEach((ch, i) => {
                    if (digits[i]) { digits[i].value = ch; digits[i].classList.add('filled'); }
                });
                if (pasted.length) digits[Math.min(pasted.length, 5)].focus();
                // Set hidden directly from the pasted string, not from DOM (avoids timing issues)
                if (!usingRecovery) hidden.value = pasted;
                if (pasted.length === 6) {
                    setTimeout(() => document.getElementById('challengeForm').requestSubmit(), 300);
                }
            });
        });

        function syncHidden() {
            if (!usingRecovery)
                hidden.value = Array.from(digits).map(d => d.value).join('');
        }

        function syncAndSubmit() {
            syncHidden();
            if (hidden.value.length === 6)
                setTimeout(() => document.getElementById('challengeForm').requestSubmit(), 300);
        }

        document.getElementById('toggleRecovery').addEventListener('click', function () {
            usingRecovery = !usingRecovery;
            if (usingRecovery) {
                otpSection.classList.add('hidden');
                recovSec.classList.remove('hidden');
                hidden.disabled = true;
                recovInput.name = 'code';
                recovInput.focus();
                this.textContent = '← Use authenticator / email code instead';
            } else {
                otpSection.classList.remove('hidden');
                recovSec.classList.add('hidden');
                hidden.disabled = false;
                recovInput.name = '';
                digits[0].focus();
                this.textContent = 'Use a recovery code instead';
            }
        });

        document.getElementById('challengeForm').addEventListener('submit', function (e) {
            if (!usingRecovery) {
                syncHidden();
                if (hidden.value.length < 6) { e.preventDefault(); digits[0].focus(); return; }
            }
            const btn = document.getElementById('verifyBtn');
            document.getElementById('btnText').classList.add('hidden');
            document.getElementById('btnLoading').classList.remove('hidden');
            document.getElementById('btnLoading').classList.add('flex');
            btn.disabled = true;
        });
    </script>

</x-guest-layout>