{{--
    resources/views/profile/two-factor-setup-totp.blade.php
    Works inside both layouts.app (customer) and layouts.admin-layout (admin).
    Uses only inline styles — no Bootstrap dependency.
--}}

@php $isAdmin = auth()->user()->isAdmin(); @endphp

@extends($isAdmin ? 'layouts.admin-layout' : 'layouts.app')

@section('title', 'Set Up Authenticator App - PageTurner')
@if($isAdmin)
    @section('page-title', 'Profile')
    @section('breadcrumb', 'Set Up 2FA')
@endif

@section('content')

<div style="max-width: 560px; margin: 2rem auto; padding: 0 1rem; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">

    {{-- Back link --}}
    <a href="{{ route('profile.edit') }}"
       style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: #64748b; font-size: 0.875rem; font-weight: 600; margin-bottom: 1.5rem; transition: color 0.2s;"
       onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#64748b'">
        ← Back to Profile
    </a>

    <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 0.25rem;">Set Up Authenticator App</h2>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0 0 2rem;">Follow the steps below to enable TOTP-based two-factor authentication.</p>

    {{-- Step Indicator --}}
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 2rem;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <div id="step1circle" style="width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #dc2626, #ef4444); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">1</div>
            <span id="step1label" style="font-size: 0.85rem; font-weight: 600; color: #dc2626;">Scan QR Code</span>
        </div>
        <div style="flex: 1; height: 2px; background: #e2e8f0; border-radius: 2px;"></div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <div id="step2circle" style="width: 34px; height: 34px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">2</div>
            <span id="step2label" style="font-size: 0.85rem; font-weight: 600; color: #94a3b8;">Verify Code</span>
        </div>
    </div>

    {{-- ── STEP 1: QR Code ── --}}
    <div id="step1" style="background: linear-gradient(145deg, #ffffff, #f8f9fa); border-radius: 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 2rem;">

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #dc2626, #ef4444); border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; box-shadow: 0 8px 20px rgba(220,38,38,0.3);">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    <path d="M14 14h3v3h-3zM17 17h3v3h-3zM14 20h3"/>
                </svg>
            </div>
            <h5 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem;">Scan with your authenticator app</h5>
            <p style="color: #64748b; font-size: 0.85rem; margin: 0;">Use Google Authenticator, Authy, Microsoft Authenticator, or any TOTP-compatible app.</p>
        </div>

        {{-- QR Code --}}
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="background: white; border: 2px solid #e2e8f0; border-radius: 16px; padding: 16px; display: inline-block; box-shadow: 0 4px 20px rgba(0,0,0,0.06);">
                <div id="qrcode"></div>
            </div>
        </div>

        {{-- Manual secret --}}
        <div style="background: linear-gradient(145deg, #f1f5f9, #e9edf2); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
            <p style="color: #64748b; font-size: 0.8rem; font-weight: 600; margin: 0 0 0.6rem;">
                🔑 Can't scan? Enter this code manually:
            </p>
            <div style="display: flex; align-items: center; gap: 8px;">
                <code id="secretCode" style="flex: 1; background: white; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 0.6rem 1rem; text-align: center; font-weight: 700; letter-spacing: 3px; font-size: 0.95rem; color: #1e293b; display: block;">
                    {{ wordwrap($secret, 4, ' ', true) }}
                </code>
                <button type="button" onclick="copySecret()"
                        style="background: white; border: 2px solid #e2e8f0; border-radius: 8px; padding: 0.5rem 0.75rem; cursor: pointer; color: #64748b; font-size: 0.85rem; transition: all 0.2s;"
                        onmouseover="this.style.borderColor='#dc2626'; this.style.color='#dc2626'"
                        onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b'"
                        title="Copy">
                    <span id="copyIcon">📋</span>
                </button>
            </div>
        </div>

        <button type="button" onclick="showStep2()"
                style="width: 100%; padding: 0.9rem; background: linear-gradient(135deg, #dc2626, #ef4444); color: white; border: none; border-radius: 50px; font-weight: 700; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 14px rgba(220,38,38,0.35); transition: all 0.3s;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 25px rgba(220,38,38,0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(220,38,38,0.35)'">
            I've Added the Account — Continue →
        </button>
    </div>

    {{-- ── STEP 2: Verify Code ── --}}
    <div id="step2" style="display: none; background: linear-gradient(145deg, #ffffff, #f8f9fa); border-radius: 20px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 2rem;">

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #dc2626, #ef4444); border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 1rem; box-shadow: 0 8px 20px rgba(220,38,38,0.3);">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h5 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 0.4rem;">Enter the 6-digit code</h5>
            <p style="color: #64748b; font-size: 0.85rem; margin: 0;">Open your authenticator app and enter the current code shown for PageTurner.</p>
        </div>

        <form method="POST" action="{{ route('two-factor.totp.confirm') }}" id="confirmForm">
            @csrf

            @if($errors->any())
                <div style="background: rgba(220,38,38,0.08); border: 1.5px solid rgba(220,38,38,0.25); color: #dc2626; border-radius: 12px; padding: 0.75rem 1rem; margin-bottom: 1.25rem; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;">
                    ✕ {{ $errors->first() }}
                </div>
            @endif

            {{-- OTP boxes --}}
            <div style="margin-bottom: 1.5rem;">
                <div style="display: flex; justify-content: center; gap: 8px;" id="otpInputs">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text"
                               class="otp-digit"
                               maxlength="1"
                               inputmode="numeric"
                               pattern="[0-9]"
                               style="width: 52px; height: 60px; text-align: center; font-size: 1.6rem; font-weight: 700; border: 2px solid #e2e8f0; border-radius: 12px; outline: none; transition: border-color 0.2s, box-shadow 0.2s; background: white; color: #1e293b;">
                    @endfor
                </div>
                <input type="hidden" name="code" id="otpHidden">
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="button" onclick="showStep1()"
                        style="padding: 0.9rem 1.5rem; background: white; border: 2px solid #e2e8f0; border-radius: 50px; font-weight: 600; font-size: 0.9rem; cursor: pointer; color: #64748b; transition: all 0.2s;"
                        onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#94a3b8'"
                        onmouseout="this.style.background='white'; this.style.borderColor='#e2e8f0'">
                    ← Back
                </button>
                <button type="submit" id="verifyBtn"
                        style="flex: 1; padding: 0.9rem; background: linear-gradient(135deg, #dc2626, #ef4444); color: white; border: none; border-radius: 50px; font-weight: 700; font-size: 0.95rem; cursor: pointer; box-shadow: 0 4px 14px rgba(220,38,38,0.35); transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-2px)'"
                        onmouseout="this.style.transform='translateY(0)'">
                    <span id="btnText">✓ Verify & Enable 2FA</span>
                    <span id="btnLoading" style="display: none;">Verifying...</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // Show step 2 automatically if validation failed (form was submitted)
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', () => showStep2());
    @endif

    document.addEventListener('DOMContentLoaded', function () {
        new QRCode(document.getElementById('qrcode'), {
            text: "{{ $qrCodeUrl }}",
            width: 200,
            height: 200,
            colorDark: '#0f172a',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    });

    function showStep1() {
        document.getElementById('step1').style.display = 'block';
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step2circle').style.background = '#e2e8f0';
        document.getElementById('step2circle').style.color = '#94a3b8';
        document.getElementById('step2label').style.color = '#94a3b8';
        document.getElementById('step1circle').style.background = 'linear-gradient(135deg, #dc2626, #ef4444)';
        document.getElementById('step1label').style.color = '#dc2626';
    }

    function showStep2() {
        document.getElementById('step1').style.display = 'none';
        document.getElementById('step2').style.display = 'block';
        document.getElementById('step2circle').style.background = 'linear-gradient(135deg, #dc2626, #ef4444)';
        document.getElementById('step2circle').style.color = 'white';
        document.getElementById('step2label').style.color = '#dc2626';
        document.getElementById('step1circle').style.background = '#e2e8f0';
        document.getElementById('step1label').style.color = '#94a3b8';
        document.querySelector('.otp-digit').focus();
    }

    // OTP digit logic
    const digits = document.querySelectorAll('.otp-digit');
    const hidden = document.getElementById('otpHidden');

    digits.forEach((input, idx) => {
        input.addEventListener('focus', () => {
            input.style.borderColor = '#dc2626';
            input.style.boxShadow = '0 0 0 4px rgba(220,38,38,0.1)';
        });
        input.addEventListener('blur', () => {
            input.style.borderColor = input.value ? '#dc2626' : '#e2e8f0';
            input.style.boxShadow = 'none';
        });
        input.addEventListener('input', e => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val;
            if (val) {
                input.style.borderColor = '#dc2626';
                input.style.color = '#dc2626';
                if (idx < 5) digits[idx + 1].focus();
            } else {
                input.style.borderColor = '#e2e8f0';
                input.style.color = '#1e293b';
            }
            syncHidden();
        });
        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                digits[idx - 1].value = '';
                digits[idx - 1].style.borderColor = '#e2e8f0';
                digits[idx - 1].style.color = '#1e293b';
                digits[idx - 1].focus();
                syncHidden();
            }
        });
        input.addEventListener('paste', e => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach((ch, i) => {
                if (digits[i]) {
                    digits[i].value = ch;
                    digits[i].style.borderColor = '#dc2626';
                    digits[i].style.color = '#dc2626';
                }
            });
            if (pasted.length > 0) digits[Math.min(pasted.length, 5)].focus();
            // Set hidden directly from pasted string to avoid DOM timing issues
            hidden.value = pasted;
        });
    });

    function syncHidden() {
        hidden.value = Array.from(digits).map(d => d.value).join('');
    }

    document.getElementById('confirmForm').addEventListener('submit', function (e) {
        syncHidden();
        if (hidden.value.length < 6) {
            e.preventDefault();
            digits[0].focus();
            return;
        }
        document.getElementById('btnText').style.display = 'none';
        document.getElementById('btnLoading').style.display = 'inline';
        document.getElementById('verifyBtn').disabled = true;
    });

    function copySecret() {
        const code = document.getElementById('secretCode').innerText.replace(/\s/g, '');
        navigator.clipboard.writeText(code).then(() => {
            document.getElementById('copyIcon').textContent = '✓';
            setTimeout(() => document.getElementById('copyIcon').textContent = '📋', 2000);
        });
    }
</script>

@endsection