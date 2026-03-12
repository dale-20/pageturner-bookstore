<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Verification Code - PageTurner</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f1f5f9; color: #1e293b; }
        .wrapper { max-width: 520px; margin: 40px auto; padding: 0 16px; }
        .card { background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #dc2626, #ef4444); padding: 36px 40px; text-align: center; }
        .header-icon { width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; }
        .header h1 { color: white; font-size: 1.4rem; font-weight: 700; letter-spacing: -0.3px; }
        .header p { color: rgba(255,255,255,0.85); font-size: 0.9rem; margin-top: 4px; }
        .body { padding: 40px; }
        .greeting { font-size: 1rem; color: #475569; margin-bottom: 24px; }
        .otp-block { background: linear-gradient(145deg, #f8fafc, #f1f5f9); border: 2px dashed #e2e8f0; border-radius: 16px; padding: 28px; text-align: center; margin-bottom: 28px; }
        .otp-label { font-size: 0.75rem; font-weight: 600; letter-spacing: 1.5px; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px; }
        .otp-code { font-size: 2.8rem; font-weight: 800; letter-spacing: 10px; color: #dc2626; font-family: 'Courier New', monospace; line-height: 1; }
        .otp-expiry { font-size: 0.8rem; color: #94a3b8; margin-top: 12px; }
        .warning { background: rgba(245, 158, 11, 0.08); border: 1.5px solid rgba(245, 158, 11, 0.25); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; }
        .warning p { font-size: 0.85rem; color: #92400e; line-height: 1.5; }
        .footer-text { font-size: 0.82rem; color: #94a3b8; line-height: 1.6; }
        .footer { background: #f8fafc; padding: 24px 40px; text-align: center; border-top: 1px solid #f1f5f9; }
        .footer p { font-size: 0.78rem; color: #cbd5e1; }
        .brand { font-weight: 700; color: #dc2626; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            {{-- Header --}}
            <div class="header">
                <div class="header-icon">
                    {{-- Simple lock SVG, no ext-gd needed --}}
                    <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </div>
                <h1>Verification Code</h1>
                <p>Two-factor authentication for PageTurner</p>
            </div>

            {{-- Body --}}
            <div class="body">
                <p class="greeting">
                    Hello! Someone is attempting to sign in to your PageTurner account.
                    Use the code below to complete your login.
                </p>

                {{-- OTP Code Block --}}
                <div class="otp-block">
                    <p class="otp-label">Your One-Time Code</p>
                    <p class="otp-code">{{ $otp }}</p>
                    <p class="otp-expiry">⏱ This code expires in <strong>10 minutes</strong></p>
                </div>

                {{-- Security Warning --}}
                <div class="warning">
                    <p>
                        ⚠️ <strong>Never share this code</strong> with anyone.
                        PageTurner will never ask for this code via email, phone, or chat.
                        If you did not attempt to log in, you can safely ignore this email.
                    </p>
                </div>

                <p class="footer-text">
                    This is an automated security email from <span class="brand">PageTurner</span>.
                    If you have trouble signing in, visit your profile to manage two-factor authentication settings.
                </p>
            </div>

            {{-- Footer --}}
            <div class="footer">
                <p>&copy; {{ date('Y') }} PageTurner. All rights reserved.</p>
            </div>

        </div>
    </div>
</body>
</html>