{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'My Profile - PageTurner')

@section('content')
<div class="container py-5">
    {{-- Header with decorative element --}}
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
            <div class="card border-0 shadow-lg profile-card animate__animated animate__fadeInLeft" style="border-radius: 20px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-body text-center p-4">
                    {{-- Profile Avatar with glow effect --}}
                    <div class="mb-4 position-relative">
                        <div class="avatar-glow"></div>
                        <div class="bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center position-relative" 
                             style="width: 120px; height: 120px; background: linear-gradient(135deg, #dc2626, #ef4444, #991b1b); background-size: 200% 200%; animation: gradientShift 5s ease infinite; box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);">
                            <span class="text-white" style="font-size: 3.5rem; font-weight: 700; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">{{ substr($user->name, 0, 1) }}</span>
                        </div>
                        
                        {{-- Online status indicator --}}
                        <div class="position-absolute bottom-0 end-0 translate-middle p-2 bg-success border border-3 border-white rounded-circle" style="width: 20px; height: 20px;"></div>
                    </div>
                    
                    <h4 class="fw-bold mb-1" style="color: #1e293b;">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>
                    
                    {{-- Role Badge with glass morphism --}}
                    <div class="mb-4">
                        <span class="badge px-4 py-2 rounded-pill" style="background: {{ $user->role == 'admin' ? 'linear-gradient(135deg, #dc2626, #ef4444)' : 'linear-gradient(135deg, #64748b, #475569)' }}; color: white; font-weight: 500; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            <i class="bi bi-shield-check me-2"></i>
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>

                    {{-- Email Verification Status --}}
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

                    {{-- Stats Cards --}}
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
                                <strong class="small">{{ $user->orders->count() ?? 0 }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Links with glass morphism --}}
            <div class="card border-0 shadow-lg mt-4 animate__animated animate__fadeInLeft animate__delay-1s" style="border-radius: 20px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="color: #1e293b; letter-spacing: 0.5px;">QUICK LINKS</h6>
                    <div class="btn-wrap align-left">
                        <a href="{{ route('orders.index') }}" class="btn-accent-arrow">My Orders</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profile Edit Form --}}
        <div class="col-md-8" style="min-height: 500px">
            <div class="card border-0 shadow-lg animate__animated animate__fadeInRight" style="border-radius: 20px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center">
                        <div>
                            <h5 class="fw-bold mb-0" style="color: #1e293b;">Edit Profile Information</h5>
                            <p class="text-muted small mb-0">Update your personal details</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    
                    {{-- Success Message with Animation --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 animate__animated animate__fadeInDown" role="alert" style="background: linear-gradient(145deg, #d1e7dd, #c0d9d0); border: none; border-left: 5px solid #198754;">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-success p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-check-lg text-white fs-5"></i>
                                    </div>
                                </div>
                                <div>
                                    <strong class="d-block">Success!</strong>
                                    <span>{{ session('success') }}</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Error Message with Animation --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 animate__animated animate__fadeInDown" role="alert" style="background: linear-gradient(145deg, #f8d7da, #f1c1c6); border: none; border-left: 5px solid #dc3545;">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-danger p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-exclamation-triangle-fill text-white fs-5"></i>
                                    </div>
                                </div>
                                <div>
                                    <strong class="d-block">Error!</strong>
                                    <span>{{ session('error') }}</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- Validation Errors Summary --}}
                    @if($errors->any())
                        <div class="alert alert-danger rounded-4 mb-4 animate__animated animate__fadeInDown" style="background: linear-gradient(145deg, #f8d7da, #f1c1c6); border: none; border-left: 5px solid #dc3545;">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-exclamation-triangle-fill text-danger fs-3"></i>
                                </div>
                                <div>
                                    <strong class="d-block mb-2">Please fix the following errors:</strong>
                                    <ul class="mb-0 ps-3">
                                        @foreach($errors->all() as $error)
                                            <li class="small">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Update Profile Form --}}
                    <form method="POST" action="{{ route('profile.update') }}" class="profile-form" id="profileForm">
                        @csrf
                        @method('PATCH')

                        {{-- Name Field --}}
                        <div class="form-floating mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                    <i class="bi bi-person text-danger"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0 rounded-end-4 @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       placeholder="Full Name"
                                       style="padding: 1rem; border-color: #e2e8f0;">
                                <label for="name" class="ms-4">Full Name</label>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Email Field --}}
                        <div class="form-floating mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                    <i class="bi bi-envelope text-danger"></i>
                                </span>
                                <input type="email" 
                                       class="form-control border-start-0 rounded-end-4 @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       placeholder="Email Address"
                                       style="padding: 1rem; border-color: #e2e8f0;">
                                <label for="email" class="ms-4">Email Address</label>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror

                            {{-- Email Verification Status --}}
                            @if(!$user->email_verified_at)
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        Not verified
                                    </span>
                                    <a href="{{ route('verification.notice') }}" class="text-danger fw-bold small">Resend verification →</a>
                                </div>
                            @endif
                        </div>

                        {{-- Password Change Section --}}
                        <div class="password-section p-4 rounded-4 mb-4" style="background: linear-gradient(145deg, #f1f5f9, #e9edf2);">
                            <h6 class="fw-bold mb-3" style="color: #1e293b;">
                                <i class="bi bi-shield-lock me-2 text-danger"></i>
                                Change Password
                            </h6>
                            <p class="text-muted small mb-4">Leave blank if you don't want to change your password</p>

                            {{-- Current Password --}}
                            <div class="form-floating mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                        <i class="bi bi-lock text-danger"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 rounded-end-4 @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password"
                                           placeholder="Current Password"
                                           style="padding: 1rem; border-color: #e2e8f0;">
                                    <label for="current_password" class="ms-4">Current Password</label>
                                </div>
                                @error('current_password')
                                    <div class="invalid-feedback d-block mt-2">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- New Password --}}
                            <div class="form-floating mb-2">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                        <i class="bi bi-key text-danger"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 rounded-end-4 @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password"
                                           placeholder="New Password"
                                           style="padding: 1rem; border-color: #e2e8f0;">
                                    <label for="password" class="ms-4">New Password</label>
                                </div>
                            </div>
                            
                            {{-- Password Strength Meter --}}
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

                            {{-- Confirm Password --}}
                            <div class="form-floating">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-end-0 rounded-start-4" style="border-color: #e2e8f0;">
                                        <i class="bi bi-key-fill text-danger"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control border-start-0 rounded-end-4" 
                                           id="password_confirmation" 
                                           name="password_confirmation"
                                           placeholder="Confirm New Password"
                                           style="padding: 1rem; border-color: #e2e8f0;">
                                    <label for="password_confirmation" class="ms-4">Confirm New Password</label>
                                </div>
                            </div>
                            
                            @error('password')
                                <div class="invalid-feedback d-block mt-2">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Submit Button with Loading State --}}
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-outline-secondary px-5 py-3 rounded-pill" style="border-width: 2px;">
                                <i class="bi bi-x-lg me-2"></i>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-danger px-5 py-3 rounded-pill shadow-lg" style="background: linear-gradient(135deg, #dc2626, #ef4444); border: none;" id="submitBtn">
                                <span class="btn-text">
                                    <i class="bi bi-check-lg me-2"></i>
                                    Save Changes
                                    <i class="bi bi-arrow-right ms-2"></i>
                                </span>
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span class="loading-text d-none">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4">

            {{-- Danger Zone --}}
            @if($user->role != 'admin')
            <div class="card border-0 shadow-lg mt-4 animate__animated animate__fadeInUp" style="border-radius: 20px; border-left: 5px solid #dc2626 !important;">
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
                    
                    {{-- Delete Account Form with Validation --}}
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
                                   id="delete_password" 
                                   name="password" 
                                   placeholder="Enter your password to confirm"
                                   required>
                            <button type="submit" class="btn btn-danger px-5 rounded-end-4" style="background: linear-gradient(135deg, #dc2626, #b91c1c); border: none;">
                                Delete Account
                            </button>
                        </div>
                        @error('delete_password')
                            <div class="invalid-feedback d-block mt-2">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    @import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');
    
    /* Gradient Animation */
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* Avatar Glow Effect */
    .avatar-glow {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 140px;
        height: 140px;
        background: radial-gradient(circle, rgba(220,38,38,0.3) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulseGlow 2s infinite;
    }
    
    @keyframes pulseGlow {
        0% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
        50% { opacity: 0.8; transform: translate(-50%, -50%) scale(1.1); }
        100% { opacity: 0.5; transform: translate(-50%, -50%) scale(1); }
    }
    
    /* Card Hover Effects */
    .profile-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .profile-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(220, 38, 38, 0.15) !important;
    }
    
    /* Form Input Styles */
    .form-control, .input-group-text {
        border: 2px solid #e2e8f0;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        border-color: #dc2626;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #dc2626;
    }
    
    /* Floating Labels */
    .form-floating > label {
        left: 55px;
        transition: all 0.3s;
        color: #64748b;
    }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        transform: scale(0.85) translateY(-0.75rem) translateX(-0.5rem);
        color: #dc2626;
        font-weight: 500;
    }
    
    /* Password Section */
    .password-section {
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    
    .password-section:hover {
        border-color: rgba(220, 38, 38, 0.2);
    }
    
    /* Progress Bar */
    .progress {
        background: #e9edf2;
        overflow: hidden;
    }
    
    .progress-bar {
        transition: width 0.3s ease, background-color 0.3s ease;
    }
    
    /* Button Styles */
    .btn {
        font-weight: 600;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn-danger {
        position: relative;
        overflow: hidden;
    }
    
    .btn-danger::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .btn-danger:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
    }
    
    .btn-outline-secondary {
        border-width: 2px;
        color: #64748b;
        border-color: #e2e8f0;
    }
    
    .btn-outline-secondary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #334155;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    /* Alert Styles */
    .alert {
        position: relative;
        padding: 1rem;
    }
    
    .alert-success {
        background: linear-gradient(145deg, #d1e7dd, #c0d9d0);
        border-left: 5px solid #198754;
    }
    
    .alert-danger {
        background: linear-gradient(145deg, #f8d7da, #f1c1c6);
        border-left: 5px solid #dc3545;
    }
    
    .alert ul {
        list-style-type: none;
        padding-left: 0;
    }
    
    .alert ul li {
        margin-bottom: 0.25rem;
        position: relative;
        padding-left: 1.5rem;
    }
    
    .alert ul li:before {
        content: "•";
        color: #dc3545;
        font-weight: bold;
        position: absolute;
        left: 0;
    }
    
    /* Invalid Feedback */
    .invalid-feedback {
        display: block;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        color: #dc2626;
    }
    
    /* Glass Morphism */
    .glass-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .btn {
            width: 100%;
        }
        
        .d-flex.justify-content-end {
            flex-direction: column;
        }
        
        .form-floating > label {
            left: 50px;
        }
    }
    
    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #dc2626, #ef4444);
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #b91c1c, #dc2626);
    }
</style>
@endsection

@section('scripts')
<script>
    // Password strength checker
    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) strength += 25;
        else feedback.push('8+ characters');
        
        if (password.match(/[a-z]+/)) strength += 25;
        else feedback.push('lowercase');
        
        if (password.match(/[A-Z]+/)) strength += 25;
        else feedback.push('uppercase');
        
        if (password.match(/[0-9]+/)) strength += 25;
        else feedback.push('number');
        
        if (password.match(/[$@#&!]+/)) strength += 25;
        
        strength = Math.min(100, strength);
        strengthBar.style.width = strength + '%';
        
        // Update strength text and color
        if (strength <= 25) {
            strengthBar.style.background = 'linear-gradient(90deg, #dc2626, #ef4444)';
            strengthText.textContent = 'Too weak';
            strengthText.style.color = '#dc2626';
        } else if (strength <= 50) {
            strengthBar.style.background = 'linear-gradient(90deg, #f97316, #fbbf24)';
            strengthText.textContent = 'Weak';
            strengthText.style.color = '#f97316';
        } else if (strength <= 75) {
            strengthBar.style.background = 'linear-gradient(90deg, #3b82f6, #60a5fa)';
            strengthText.textContent = 'Good';
            strengthText.style.color = '#3b82f6';
        } else {
            strengthBar.style.background = 'linear-gradient(90deg, #22c55e, #4ade80)';
            strengthText.textContent = 'Strong';
            strengthText.style.color = '#22c55e';
        }
        
        if (password.length === 0) {
            strengthBar.style.width = '0%';
            strengthText.textContent = 'Too weak';
        }
        
        // Update help text with feedback
        const helpText = document.getElementById('passwordHelp');
        if (feedback.length > 0 && password.length > 0) {
            helpText.innerHTML = `Missing: ${feedback.join(', ')}`;
            helpText.style.color = '#f97316';
        } else if (password.length > 0) {
            helpText.innerHTML = '✓ Strong password!';
            helpText.style.color = '#22c55e';
        } else {
            helpText.innerHTML = 'Use at least 8 characters with letters & numbers';
            helpText.style.color = '#64748b';
        }
    });
    
    // Form submission loading state
    document.getElementById('profileForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = submitBtn.querySelector('.btn-text');
        const loadingText = submitBtn.querySelector('.loading-text');
        const spinner = submitBtn.querySelector('.spinner-border');
        
        btnText.classList.add('d-none');
        loadingText.classList.remove('d-none');
        spinner.classList.remove('d-none');
        submitBtn.disabled = true;
    });
    
    // Floating labels enhancement
    document.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.classList.add('filled');
            } else {
                this.classList.remove('filled');
            }
        });
    });
    
    // Smooth scroll to error
    if (document.querySelector('.is-invalid')) {
        document.querySelector('.is-invalid').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center' 
        });
    }
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            if (!alert.classList.contains('alert-warning')) {
                alert.classList.remove('show');
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        });
    }, 5000);
</script>
@endsection