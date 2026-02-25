{{-- 
    Shared partial: resources/views/profile/_content.blade.php
    Included by both admin-edit.blade.php and customer-edit.blade.php
--}}

@php
    $isAdmin = auth()->user()->isAdmin();
@endphp

<div class="container py-5">
    {{-- Header --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div>
                    <h2 class="fw-bold mb-1">My Profile</h2>
                    <p class="text-muted mb-0">Manage your account settings and preferences</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Profile Sidebar --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-lg profile-card" style="border-radius: 20px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-body text-center p-4">
                    {{-- Avatar with upload --}}
                    <div class="mb-4 position-relative d-inline-block">
                        <div class="avatar-glow"></div>

                        {{-- Avatar display --}}
                        <div id="avatarWrapper" class="rounded-circle overflow-hidden d-inline-flex align-items-center justify-content-center position-relative"
                             style="width: 120px; height: 120px; background: linear-gradient(135deg, #dc2626, #ef4444, #991b1b); box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3); cursor: pointer;"
                             onclick="document.getElementById('profile_photo').click()"
                             title="Click to change photo">
                            @if($user->profile_photo)
                                <img id="avatarPreview"
                                     src="{{ asset('storage/' . $user->profile_photo) }}"
                                     alt="{{ $user->name }}"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img id="avatarPreview" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                                <span id="avatarInitial" class="text-white" style="font-size: 3.5rem; font-weight: 700;">{{ substr($user->name, 0, 1) }}</span>
                            @endif

                            {{-- Hover overlay --}}
                            <div class="avatar-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column align-items-center justify-content-center rounded-circle"
                                 style="background: rgba(0,0,0,0.45); opacity: 0; transition: opacity 0.2s;">
                                <i class="bi bi-camera-fill text-white" style="font-size: 1.5rem;"></i>
                                <small class="text-white" style="font-size: 0.65rem;">Change photo</small>
                            </div>
                        </div>

                        {{-- Online indicator --}}
                        <div class="position-absolute bottom-0 end-0 p-2 bg-success border border-3 border-white rounded-circle" style="width: 20px; height: 20px;"></div>
                    </div>

                    <h4 class="fw-bold mb-1" style="color: #1e293b;">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    {{-- Role Badge --}}
                    <div class="mb-4">
                        <span class="badge px-4 py-2 rounded-pill"
                              style="background: {{ $isAdmin ? 'linear-gradient(135deg, #dc2626, #ef4444)' : 'linear-gradient(135deg, #64748b, #475569)' }}; color: white; font-weight: 500; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <i class="bi bi-shield-check me-2"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>

                    {{-- Email Verification --}}
                    @if($user->email_verified_at)
                        <div class="alert alert-success py-2 px-3 mb-4 rounded-pill d-inline-flex align-items-center" style="background: rgba(25, 135, 84, 0.1); border: none; color: #198754;">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <small>Email verified</small>
                        </div>
                    @else
                        <div class="alert alert-warning py-2 px-3 mb-4 rounded-pill" style="background: rgba(255, 193, 7, 0.1); border: none; color: #856404;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <small>Email not verified</small>
                            <a href="{{ route('verification.notice') }}" class="text-primary fw-bold d-block mt-1">Verify now →</a>
                        </div>
                    @endif

                    {{-- Stats --}}
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="p-3 rounded-4" style="background: rgba(220, 38, 38, 0.05);">
                                <i class="bi bi-calendar3 text-danger mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <small class="text-muted d-block">Member since</small>
                                <strong class="small">{{ $user->created_at->format('M d, Y') }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-4" style="background: rgba(220, 38, 38, 0.05);">
                                <i class="bi bi-box-seam text-danger mb-2 d-block" style="font-size: 1.2rem;"></i>
                                <small class="text-muted d-block">Orders</small>
                                <strong class="small">{{ $user->orders->count() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="card border-0 shadow-lg mt-4" style="border-radius: 20px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="color: #1e293b; letter-spacing: 0.5px;">QUICK LINKS</h6>
                    @if($isAdmin)
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-danger btn-sm rounded-pill">Dashboard</a>
                            <a href="{{ route('admin.orders', 'pending') }}" class="btn btn-outline-danger btn-sm rounded-pill">Manage Orders</a>
                            <a href="{{ route('admin.books.index') }}" class="btn btn-outline-danger btn-sm rounded-pill">Manage Books</a>
                        </div>
                    @else
                        <div class="d-grid gap-2">
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-danger btn-sm rounded-pill">My Orders</a>
                            <a href="{{ route('books.index') }}" class="btn btn-outline-danger btn-sm rounded-pill">Browse Books</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Profile Edit Form --}}
        <div class="col-md-8" style="min-height: 500px">
            <div class="card border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0" style="color: #1e293b;">Edit Profile Information</h5>
                    <p class="text-muted small mb-0">Update your personal details</p>
                </div>

                <div class="card-body p-4">

                    {{-- Toasts are rendered at bottom of file, no inline alerts needed --}}

                    {{-- Update Profile Form --}}
                    <form method="POST" action="{{ route('profile.update') }}" id="profileForm" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        {{-- Hidden file input for avatar --}}
                        <input type="file" id="profile_photo" name="profile_photo"
                               accept="image/*" class="d-none">

                        {{-- Name --}}
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                    <i class="bi bi-person text-danger"></i>
                                </span>
                                <input type="text"
                                       class="form-control border-start-0 rounded-end-4 @error('name') is-invalid @enderror"
                                       id="name" name="name"
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Full Name"
                                       style="padding: 1rem; border-color: #e2e8f0;">
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                    <i class="bi bi-envelope text-danger"></i>
                                </span>
                                <input type="email"
                                       class="form-control border-start-0 rounded-end-4 @error('email') is-invalid @enderror"
                                       id="email" name="email"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="Email Address"
                                       style="padding: 1rem; border-color: #e2e8f0;">
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}
                                </div>
                            @enderror
                            @if(!$user->email_verified_at)
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                        <i class="bi bi-exclamation-triangle me-1"></i> Not verified
                                    </span>
                                    <a href="{{ route('verification.notice') }}" class="text-danger fw-bold small">Resend verification →</a>
                                </div>
                            @endif
                        </div>

                        {{-- Change Password --}}
                        <div class="password-section p-4 rounded-4 mb-4" style="background: linear-gradient(145deg, #f1f5f9, #e9edf2);">
                            <h6 class="fw-bold mb-1" style="color: #1e293b;">
                                <i class="bi bi-shield-lock me-2 text-danger"></i>Change Password
                            </h6>
                            <p class="text-muted small mb-4">Leave blank if you don't want to change your password</p>

                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                        <i class="bi bi-lock text-danger"></i>
                                    </span>
                                    <input type="password"
                                           class="form-control border-start-0 rounded-end-4 @error('current_password') is-invalid @enderror"
                                           id="current_password" name="current_password"
                                           placeholder="Current Password"
                                           style="padding: 1rem; border-color: #e2e8f0;">
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block mt-2">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                        <i class="bi bi-key text-danger"></i>
                                    </span>
                                    <input type="password"
                                           class="form-control border-start-0 rounded-end-4 @error('password') is-invalid @enderror"
                                           id="password" name="password"
                                           placeholder="New Password"
                                           style="padding: 1rem; border-color: #e2e8f0;">
                                </div>
                            </div>

                            {{-- Password Strength --}}
                            <div class="password-strength mb-3 px-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-muted">Password strength</small>
                                    <small class="text-muted" id="strengthText">Too weak</small>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" role="progressbar" style="width: 0%; border-radius: 10px;" id="passwordStrength"></div>
                                </div>
                                <small class="text-muted mt-2 d-block" id="passwordHelp">Use at least 8 characters with letters & numbers</small>
                            </div>

                            <div class="mb-0">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                        <i class="bi bi-key-fill text-danger"></i>
                                    </span>
                                    <input type="password"
                                           class="form-control border-start-0 rounded-end-4"
                                           id="password_confirmation" name="password_confirmation"
                                           placeholder="Confirm New Password"
                                           style="padding: 1rem; border-color: #e2e8f0;">
                                </div>
                            </div>

                            @error('password')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-outline-secondary px-5 py-3 rounded-pill" style="border-width: 2px;">
                                <i class="bi bi-x-lg me-2"></i>Cancel
                            </button>
                            <button type="submit" class="btn btn-danger px-5 py-3 rounded-pill shadow-lg"
                                    style="background: linear-gradient(135deg, #dc2626, #ef4444); border: none;" id="submitBtn">
                                <span class="btn-text">
                                    <i class="bi bi-check-lg me-2"></i>Save Changes<i class="bi bi-arrow-right ms-2"></i>
                                </span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                <span class="loading-text d-none">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4">

            {{-- Danger Zone — customers only --}}
            @if(!$isAdmin)
            <div class="card border-0 shadow-lg mt-4" style="border-radius: 20px; border-left: 5px solid #dc2626 !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3" style="width: 50px; height: 50px; background: rgba(220, 38, 38, 0.1); border-radius: 15px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-exclamation-triangle-fill text-danger fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-danger mb-0">Danger Zone</h5>
                            <p class="text-muted small mb-0">Irreversible account actions</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.destroy') }}"
                          onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                <i class="bi bi-shield-lock text-danger"></i>
                            </span>
                            <input type="password"
                                   class="form-control border-start-0 @error('delete_password') is-invalid @enderror"
                                   id="delete_password" name="password"
                                   placeholder="Enter your password to confirm"
                                   required>
                            <button type="submit" class="btn btn-danger px-5 rounded-end-4"
                                    style="background: linear-gradient(135deg, #dc2626, #b91c1c); border: none;">
                                Delete Account
                            </button>
                        </div>
                        @error('delete_password')
                            <div class="invalid-feedback d-block mt-2">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> {{ $message }}
                            </div>
                        @enderror
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 9999;">

    @if(session('status') === 'profile-updated' || session('success'))
    <div id="toastSuccess" class="toast align-items-center border-0 text-white show" role="alert"
         style="background: linear-gradient(135deg, #16a34a, #22c55e); border-radius: 16px; min-width: 300px; box-shadow: 0 8px 24px rgba(22,163,74,0.35);">
        <div class="d-flex align-items-center p-3">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-3"
                 style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="bi bi-check-lg fs-5"></i>
            </div>
            <div class="me-auto">
                <strong class="d-block">Success!</strong>
                <small>{{ session('success') ?? 'Profile updated successfully.' }}</small>
            </div>
            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-progress" style="height: 4px; background: rgba(255,255,255,0.4); border-radius: 0 0 16px 16px; overflow: hidden;">
            <div id="toastSuccessBar" style="height: 100%; width: 100%; background: rgba(255,255,255,0.8); transition: width linear;"></div>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div id="toastError" class="toast align-items-center border-0 text-white show" role="alert"
         style="background: linear-gradient(135deg, #dc2626, #ef4444); border-radius: 16px; min-width: 300px; box-shadow: 0 8px 24px rgba(220,38,38,0.35);">
        <div class="d-flex align-items-center p-3">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-3"
                 style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            </div>
            <div class="me-auto">
                <strong class="d-block">Fix the following:</strong>
                <ul class="mb-0 ps-3 mt-1" style="font-size: 0.8rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="btn-close btn-close-white ms-3 align-self-start mt-1" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-progress" style="height: 4px; background: rgba(255,255,255,0.4); border-radius: 0 0 16px 16px; overflow: hidden;">
            <div id="toastErrorBar" style="height: 100%; width: 100%; background: rgba(255,255,255,0.8); transition: width linear;"></div>
        </div>
    </div>
    @endif

</div>

{{-- Shared styles --}}
<style>
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    .avatar-glow {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        width: 140px; height: 140px;
        background: radial-gradient(circle, rgba(220,38,38,0.3) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulseGlow 2s infinite;
    }
    @keyframes pulseGlow {
        0%   { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
        50%  { opacity: 0.8; transform: translate(-50%, -50%) scale(1.1); }
        100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
    }
    .profile-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .profile-card:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(220, 38, 38, 0.15) !important; }
    .form-control, .input-group-text { border: 2px solid #e2e8f0; transition: all 0.3s; }
    .form-control:focus { border-color: #dc2626; box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1); }
    .input-group:focus-within .input-group-text { border-color: #dc2626; }
    .password-section { border: 2px solid transparent; transition: all 0.3s; }
    .password-section:hover { border-color: rgba(220, 38, 38, 0.2); }
    .progress { background: #e9edf2; overflow: hidden; }
    .progress-bar { transition: width 0.3s ease, background-color 0.3s ease; }
    .btn { font-weight: 600; letter-spacing: 0.3px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4); }
    .btn-outline-secondary { border-width: 2px; color: #64748b; border-color: #e2e8f0; }
    .btn-outline-secondary:hover { background: #f1f5f9; border-color: #94a3b8; color: #334155; transform: translateY(-2px); }
    .invalid-feedback { display: block; font-size: 0.875rem; margin-top: 0.25rem; color: #dc2626; }
    .alert ul { list-style-type: none; padding-left: 0; }
    .alert ul li { margin-bottom: 0.25rem; position: relative; padding-left: 1.5rem; }
    .alert ul li:before { content: "•"; color: #dc3545; font-weight: bold; position: absolute; left: 0; }
</style>

{{-- Shared scripts --}}
<script>
    document.getElementById('password').addEventListener('input', function () {
        const password = this.value;
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        const helpText = document.getElementById('passwordHelp');
        let strength = 0, feedback = [];
        if (password.length >= 8) strength += 25; else feedback.push('8+ characters');
        if (password.match(/[a-z]+/)) strength += 25; else feedback.push('lowercase');
        if (password.match(/[A-Z]+/)) strength += 25; else feedback.push('uppercase');
        if (password.match(/[0-9]+/)) strength += 25; else feedback.push('number');
        strength = Math.min(100, strength);
        strengthBar.style.width = strength + '%';
        if (strength <= 25)      { strengthBar.style.background = 'linear-gradient(90deg, #dc2626, #ef4444)'; strengthText.textContent = 'Too weak';  strengthText.style.color = '#dc2626'; }
        else if (strength <= 50) { strengthBar.style.background = 'linear-gradient(90deg, #f97316, #fbbf24)'; strengthText.textContent = 'Weak';      strengthText.style.color = '#f97316'; }
        else if (strength <= 75) { strengthBar.style.background = 'linear-gradient(90deg, #3b82f6, #60a5fa)'; strengthText.textContent = 'Good';      strengthText.style.color = '#3b82f6'; }
        else                     { strengthBar.style.background = 'linear-gradient(90deg, #22c55e, #4ade80)'; strengthText.textContent = 'Strong';    strengthText.style.color = '#22c55e'; }
        if (password.length === 0) { strengthBar.style.width = '0%'; strengthText.textContent = 'Too weak'; }
        helpText.innerHTML = feedback.length > 0 && password.length > 0
            ? `Missing: ${feedback.join(', ')}`
            : password.length > 0 ? '✓ Strong password!' : 'Use at least 8 characters with letters & numbers';
        helpText.style.color = feedback.length > 0 ? '#f97316' : password.length > 0 ? '#22c55e' : '#64748b';
    });

    document.getElementById('profileForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.querySelector('.btn-text').classList.add('d-none');
        btn.querySelector('.loading-text').classList.remove('d-none');
        btn.querySelector('.spinner-border').classList.remove('d-none');
        btn.disabled = true;
    });

    // Avatar hover effect
    const avatarWrapper = document.getElementById('avatarWrapper');
    const overlay = avatarWrapper.querySelector('.avatar-overlay');
    avatarWrapper.addEventListener('mouseenter', () => overlay.style.opacity = '1');
    avatarWrapper.addEventListener('mouseleave', () => overlay.style.opacity = '0');

    // Avatar preview on file select
    document.getElementById('profile_photo').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function (e) {
            const preview = document.getElementById('avatarPreview');
            const initial = document.getElementById('avatarInitial');
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (initial) initial.style.display = 'none';
        };
        reader.readAsDataURL(file);
    });
    function initToast(toastId, barId, duration) {
        const toastEl = document.getElementById(toastId);
        const bar = document.getElementById(barId);
        if (!toastEl || !bar) return;
        const toast = new bootstrap.Toast(toastEl, { autohide: false });
        toast.show();
        bar.style.transition = `width ${duration}ms linear`;
        setTimeout(() => bar.style.width = '0%', 50);
        setTimeout(() => toast.hide(), duration);
    }
    initToast('toastSuccess', 'toastSuccessBar', 5000);
    initToast('toastError',   'toastErrorBar',   8000);

    if (document.querySelector('.is-invalid')) {
        document.querySelector('.is-invalid').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>