{{-- resources/views/admin/userShow.blade.php --}}
@extends('layouts.admin-layout')

@section('title', $user->name . ' - User Profile - PageTurner')
@section('page-title', 'User Profile')
@section('breadcrumb', 'User Details')

@section('content')
    <!-- User Profile Card -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-4">
                        <!-- User Avatar -->
                        <div class="avatar-image" style="width: 120px; height: 120px; border-radius: 15px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                                @if(!empty($user->profile_photo) && file_exists(public_path('storage/' . $user->profile_photo)))
                                                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                                        alt="{{ $user->name }} cover"
                                                        class="img-fluid"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center w-100 h-100 stat-box" 
                                                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                        <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                        </div>
                        
                        <!-- User Info -->
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h2 class="fw-bold mb-2">{{ $user->name }}</h2>
                                    <p class="text-muted mb-3">
                                        <i class="feather feather-mail me-1"></i> {{ $user->email }}
                                    </p>
                                </div>
                                
                                <!-- User Status -->
                                <div class="text-end">
                                    @if($user->deleted_at)
                                        <span class="badge bg-danger px-3 py-2">Account Deleted</span>
                                    @else
                                        <span class="badge bg-success px-3 py-2">Active Account</span>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Stats Cards -->
                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <div class="stat-card p-3 stat-box rounded-3">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon me-3">
                                                <i class="feather feather-shopping-bag fs-2 text-primary"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Total Orders</small>
                                                <span class="fw-bold h4 mb-0">{{ $user->orders_count }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card p-3 stat-box rounded-3">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon me-3">
                                                <i class="feather feather-check-circle fs-2 text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Completed Orders</small>
                                                <span class="fw-bold h4 mb-0">{{ $user->orders()->where('status', 'completed')->count() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card p-3 stat-box rounded-3">
                                        <div class="d-flex align-items-center">
                                            <div class="stat-icon me-3">
                                                <i class="feather feather-dollar-sign fs-2 text-warning"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Total Spent</small>
                                               @php
                                                    $completedTotal = $user->orders()->where('status', 'completed')->sum('total_amount');
                                               @endphp

                                                <span class="fw-bold h4 mb-0 text-success">
                                                    ₱{{ number_format($completedTotal, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Details -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title">Account Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%" class="text-muted">Account Status:</td>
                                    <td>
                                        @if($user->deleted_at)
                                            <span class="badge bg-danger">Deleted</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email Verification:</td>
                                    <td>
                                        @if($user->email_verified_at)
                                            <span class="badge bg-success">
                                                <i class="feather feather-check-circle me-1"></i> Verified
                                            </span>
                                            <small class="text-muted d-block mt-1">{{ $user->email_verified_at->format('M d, Y h:i A') }}</small>
                                        @else
                                            <span class="badge bg-warning">Not Verified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Member Since:</td>
                                    <td>
                                        <strong>{{ $user->created_at->format('F d, Y') }}</strong>
                                        <small class="text-muted d-block">{{ $user->created_at->format('h:i A') }}</small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%" class="text-muted">Last Updated:</td>
                                    <td>
                                        <strong>{{ $user->updated_at->format('F d, Y') }}</strong>
                                        <small class="text-muted d-block">{{ $user->updated_at->format('h:i A') }}</small>
                                    </td>
                                </tr>
                                @if($user->deleted_at)
                                <tr>
                                    <td class="text-muted">Deleted At:</td>
                                    <td>
                                        <span class="text-danger">
                                            <strong>{{ $user->deleted_at->format('F d, Y') }}</strong>
                                            <small class="text-muted d-block">{{ $user->deleted_at->format('h:i A') }}</small>
                                        </span>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <div class="card stretch stretch-full">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="userOrdersList">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Book Title</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Date Ordered</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($user->orders()->with('orderItems.book')->latest()->get() as $order)
                                    <tr class="single-item">
                                        <td>
                                            <span class="fw-semibold">#{{ $order->id }}</span>
                                        </td>
                                        <td>
                                            @php $items = $order->orderItems; @endphp
                                            <span class="fw-semibold">{{ $items->first()->book->title ?? 'N/A' }}</span>
                                            <small class="text-muted d-block">x{{ $items->first()->quantity ?? 1 }}</small>
                                            @if($items->count() > 1)
                                                <a class="small text-primary" data-bs-toggle="collapse"
                                                   href="#userOrderItems_{{ $order->id }}" role="button">
                                                    +{{ $items->count() - 1 }} more
                                                </a>
                                                <div class="collapse mt-1" id="userOrderItems_{{ $order->id }}">
                                                    @foreach($items->skip(1) as $item)
                                                        <div class="small text-muted">
                                                            {{ $item->book->title ?? 'N/A' }}
                                                            <span class="text-body">x{{ $item->quantity }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">₱{{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending'    => 'bg-warning',
                                                    'processing' => 'bg-info',
                                                    'completed'  => 'bg-success',
                                                    'cancelled'  => 'bg-danger',
                                                ][$order->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $statusClass }} px-3 py-2">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span>{{ $order->created_at->format('M d, Y') }}</span>
                                                <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="hstack gap-2 justify-content-end">
                                                <a href="{{ route('admin.orderShow', $order->id) }}"
                                                   class="avatar-text avatar-md"
                                                   data-bs-toggle="tooltip"
                                                   title="View Order">
                                                    <i class="feather feather-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="feather feather-shopping-bag" style="font-size: 3rem; opacity: 0.5;"></i>
                                            <p class="mt-2 text-muted">This user hasn't placed any orders yet</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row mt-3">
        <div class="col-lg-12">
            <a href="{{ route('admin.users', 'customer') }}" class="btn btn-light">
                <i class="feather feather-arrow-left me-2"></i> Back to Users
            </a>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
@endsection

@push('styles')
<style>
    /* Dark-mode aware stat boxes */
    .stat-box {
        background-color: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        transition: background-color 0.2s;
    }
    .stat-value { color: var(--bs-body-color); }
    .stat-label { font-size: 0.82rem; font-weight: 500; color: var(--bs-secondary-color); }
</style>
@endpush