@php
    $user = auth()->user()

@endphp

<div class="dropdown nxl-h-item">
    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
        <img src="{{ empty($user->profile_photo) ? asset('duralex/images/profile_default.png') : asset('storage/' . $user->profile_photo)}}" alt="user-image"
            class="img-fluid user-avtar me-0" />
    </a>
    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
        <div class="dropdown-header">
            <div class="d-flex align-items-center">
                <img src="{{ empty($user->profile_photo) ? asset('duralex/images/profile_default.png') : asset('storage/' . $user->profile_photo)}}" alt="user-image"
                    class="img-fluid user-avtar" />
                <div>
                    <h6 class="text-dark mb-0"> {{ $user->name }}</h6>
                    <span class="fs-12 fw-medium text-muted">{{ $user->email }}</span>
                </div>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <a href="{{ route('profile.edit') }}" class="dropdown-item">
            <i class="feather-user"></i>
            <span>Profile Details</span>
        </a>
        {{-- <a href="javascript:void(0);" class="dropdown-item">
            <i class="feather-activity"></i>
            <span>Activity Feed</span>
        </a>
        <a href="javascript:void(0);" class="dropdown-item">
            <i class="feather-dollar-sign"></i>
            <span>Billing Details</span>
        </a>
        <a href="javascript:void(0);" class="dropdown-item">
            <i class="feather-bell"></i>
            <span>Notifications</span>
        </a>
        <a href="javascript:void(0);" class="dropdown-item">
            <i class="feather-settings"></i>
            <span>Account Settings</span>
        </a> --}}
        <div class="dropdown-divider"></div>
        {{-- <a href="./auth-login-minimal.html" class="dropdown-item">
            <i class="feather-log-out"></i>
            <span>Logout</span>
        </a> --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dropdown-item">
                <i class="feather-log-out"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</div>