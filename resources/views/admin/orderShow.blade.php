@extends('layouts.admin-layout')

@section('title', 'Order #' . $order->id . ' - PageTurner Admin')
@section('page-title', 'Order Management')
@section('breadcrumb', 'Order #' . $order->id)

@section('content')
    @php
        // Status configuration (matching the user view style)
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'bg' => '#fff3cd',
                'border' => '#ffeeba',
                'text' => '#856404',
                'icon' => 'clock',
                'message' => 'This order is waiting to be processed.',
                'nextAction' => 'processing',
                'nextActionText' => 'Accept Order',
                'nextActionIcon' => 'check',
                'nextActionColor' => 'success'
            ],
            'processing' => [
                'color' => 'info',
                'bg' => '#d1ecf1',
                'border' => '#bee5eb',
                'text' => '#0c5460',
                'icon' => 'refresh-cw',
                'message' => 'This order is being prepared.',
                'nextAction' => 'completed',
                'nextActionText' => 'Complete Order',
                'nextActionIcon' => 'check-circle',
                'nextActionColor' => 'primary'
            ],
            'completed' => [
                'color' => 'success',
                'bg' => '#d4edda',
                'border' => '#c3e6cb',
                'text' => '#155724',
                'icon' => 'check-circle',
                'message' => 'This order has been completed successfully.',
                'nextAction' => null
            ],
            'cancelled' => [
                'color' => 'danger',
                'bg' => '#f8d7da',
                'border' => '#f5c6cb',
                'text' => '#721c24',
                'icon' => 'x-circle',
                'message' => 'This order has been cancelled.',
                'nextAction' => null
            ]
        ];
        
        $currentStatus = $statusConfig[$order->status] ?? $statusConfig['pending'];
        
        // Calculate order totals
        // Derive subtotal from total_amount since item prices may not be stored on order items
        $shipping = 5.00; // Flat shipping rate
        // total_amount = subtotal + tax + shipping => subtotal * 1.1 = total_amount - shipping
        $subtotal = ($order->total_amount - $shipping) / 1.1;
        $tax = $subtotal * 0.1; // 10% tax
    @endphp

    <div class="main-content">
        <div class="container-fluid">
            {{-- Status Banner (similar to user view) --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm" 
                         style="border-left: 5px solid {{ $currentStatus['text'] }} !important; border-radius: 16px;">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div class="d-flex align-items-center mb-2 mb-md-0">
                                    <div class="status-icon me-3" 
                                         style="background: {{ $currentStatus['bg'] }}; padding: 12px; border-radius: 50%;">
                                        @if($currentStatus['icon'] == 'clock')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                        @elseif($currentStatus['icon'] == 'refresh-cw')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                            </svg>
                                        @elseif($currentStatus['icon'] == 'check-circle')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        @elseif($currentStatus['icon'] == 'x-circle')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="badge p-2 mb-1" 
                                               style="background: {{ $currentStatus['bg'] }}; color: {{ $currentStatus['text'] }}; font-size: 1rem;">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                        <h5 class="text-muted mb-0">Order #{{ $order->id }}</h5>
                                    </div>
                                </div>
                                <div class="text-md-end">
                                    <h4 class="mb-0">Total: ${{ number_format($order->total_amount, 2) }}</h4>
                                    <small class="text-muted">{{ $order->orderItems->sum('quantity') }} item(s) • 
                                               {{ $order->created_at->format('M d, Y \a\t h:i A') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Main Content - Customer & Order Details --}}
                <div class="col-lg-8">
                    {{-- Customer Information Card --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0;">
                            <h5 class="mb-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                Customer Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-image avatar-lg me-3" 
                                             style="width: 60px; height: 60px; border-radius: 50%; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                            @if($order->user && $order->user->profile_photo)
                                                <img src="{{ asset('storage/' . $order->user->profile_photo) }}" 
                                                     alt="{{ $order->user->name }}" 
                                                     class="img-fluid rounded-circle" 
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                            @else
                                                <span style="font-size: 24px; color: #999;">
                                                    {{ strtoupper(substr($order->user->name ?? 'U', 0, 1)) }}
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <h5 class="mb-1">{{ $order->user->name ?? 'N/A' }}</h5>
                                            <p class="text-muted mb-0">{{ $order->user->email ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td class="text-muted ps-0">Member since:</td>
                                            <td class="fw-semibold text-end pe-0">
                                                {{ $order->user->created_at ? $order->user->created_at->format('M d, Y') : 'N/A' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0">Total Orders:</td>
                                            <td class="fw-semibold text-end pe-0">
                                                {{ $order->user->orders->count() ?? 0 }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted ps-0">Total Spent:</td>
                                            <td class="fw-semibold text-end pe-0">
                                                ${{ number_format($order->user->orders->where('status', 'completed')->sum('total_amount'), 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Items Card --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" 
                             style="border-radius: 16px 16px 0 0;">
                            <h5 class="mb-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                Order Items
                            </h5>
                            <span class="badge bg-primary">{{ $order->orderItems->count() }} item(s)</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 text-dark">Book Details</th>
                                            <th class="text-center text-dark">Quantity</th>
                                            <th class="text-end text-dark">Unit Price</th>
                                            <th class="text-end pe-4 text-dark">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->orderItems as $item)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        @if($item->book && $item->book->cover_image)
                                                            <img src="{{ asset('storage/' . $item->book->cover_image) }}" 
                                                                 alt="{{ $item->book->title }}"
                                                                 style="width: 50px; height: 60px; object-fit: cover; border-radius: 8px;"
                                                                 class="me-3">
                                                        @else
                                                            <div class="bg-secondary bg-opacity-10 rounded me-3 d-flex align-items-center justify-content-center"
                                                                 style="width: 50px; height: 60px; border-radius: 8px;">
                                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2">
                                                                    <rect x="2" y="3" width="20" height="18" rx="2" ry="2"></rect>
                                                                    <line x1="8" y1="9" x2="16" y2="9"></line>
                                                                    <line x1="8" y1="13" x2="16" y2="13"></line>
                                                                    <line x1="8" y1="17" x2="12" y2="17"></line>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-1">{{ $item->book->title ?? 'N/A' }}</h6>
                                                            <small class="text-muted d-block">by {{ $item->book->author ?? 'N/A' }}</small>
                                                            <small class="text-muted">ISBN: {{ $item->book->isbn ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <span class="fw-semibold">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="text-end align-middle">
                                                    ${{ number_format($item->price ?: $item->book->price, 2) }}
                                                </td>
                                                <td class="text-end pe-4 align-middle fw-semibold">
                                                    ${{ number_format(($item->price ?: $item->book->price) * $item->quantity, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Order Timeline --}}
                    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0;">
                            <h5 class="mb-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                Order Timeline
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline-steps">
                                <div class="timeline-step {{ $order->created_at ? 'completed' : '' }}">
                                    <div class="timeline-icon bg-primary text-white">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                        </svg>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Placed</h6>
                                        <p class="text-muted small mb-0">{{ $order->created_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>

                                <div class="timeline-step {{ in_array($order->status, ['processing', 'completed']) ? 'completed' : '' }}">
                                    <div class="timeline-icon {{ in_array($order->status, ['processing', 'completed']) ? 'bg-info' : 'bg-secondary' }} text-white">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                        </svg>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Processed</h6>
                                        @if(in_array($order->status, ['processing', 'completed']))
                                            <p class="text-muted small mb-0">{{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                        @else
                                            <p class="text-muted small mb-0">Pending</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="timeline-step {{ $order->status === 'completed' ? 'completed' : '' }}">
                                    <div class="timeline-icon {{ $order->status === 'completed' ? 'bg-success' : 'bg-secondary' }} text-white">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order Status</h6>
                                        @if($order->status === 'completed')
                                            <p class="text-muted small mb-0">{{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                        @else
                                            <p class="text-muted small mb-0">Pending</p>
                                        @endif
                                    </div>
                                </div>

                                @if($order->status === 'cancelled')
                                    <div class="timeline-step completed">
                                        <div class="timeline-icon bg-danger text-white">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Order Cancelled</h6>
                                            <p class="text-muted small mb-0">{{ $order->updated_at->format('M d, Y h:i A') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar - Admin Actions --}}
                <div class="col-lg-4">
                    {{-- Status Message Card --}}
                    <div class="card border-0 shadow-sm mb-4" 
                         style="background: {{ $currentStatus['bg'] }}; border-color: {{ $currentStatus['border'] }}; border-radius: 16px;">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="me-3">
                                    @if($currentStatus['icon'] == 'clock')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    @elseif($currentStatus['icon'] == 'refresh-cw')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                        </svg>
                                    @elseif($currentStatus['icon'] == 'check-circle')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    @elseif($currentStatus['icon'] == 'x-circle')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <h6 style="color: {{ $currentStatus['text'] }}; font-weight: 600;">
                                        {{ ucfirst($order->status) }} Order
                                    </h6>
                                    <p style="color: {{ $currentStatus['text'] }}; opacity: 0.9; margin-bottom: 0; font-size: 0.9rem;">
                                        {{ $currentStatus['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Admin Actions Card --}}
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-white py-3" style="border-radius: 16px 16px 0 0;">
                            <h5 class="mb-0">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path d="M12 5v14M5 12h14"></path>
                                </svg>
                                Admin Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            {{-- Status Update Forms --}}
                            @if($order->status === 'pending')
                                <div class="mb-4">
                                    <label class="form-label fw-bold mb-3">Update Order Status</label>
                                    <div class="d-grid gap-2">
                                        <form action="{{ route('admin.order.status', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="processing">
                                            <button type="submit" class="btn btn-success w-100 mb-2" 
                                                    onclick="return confirm('Process this order?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                                    <polyline points="20 6 9 17 4 12"></polyline>
                                                </svg>
                                                Accept Order (Move to Processing)
                                            </button>
                                        </form>
                                        
                                        <form action="{{ route('admin.order.status', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-outline-danger w-100" 
                                                    onclick="return confirm('Cancel this order?')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                </svg>
                                                Deny Order (Cancel)
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            @if($order->status === 'processing')
                                <div class="mb-4">
                                    <label class="form-label fw-bold mb-3">Update Order Status</label>
                                    <form action="{{ route('admin.order.status', $order) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-primary w-100" 
                                                onclick="return confirm('Mark this order as completed?')">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                            Complete Order
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- Quick Actions --}}
                            <div class="border-top pt-3">
                                <h6 class="fw-bold mb-3">Quick Actions</h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('admin.orders', $order->status) }}" class="btn btn-light">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                            <line x1="19" y1="12" x2="5" y2="12"></line>
                                            <polyline points="12 19 5 12 12 5"></polyline>
                                        </svg>
                                        Back to {{ ucfirst($order->status) }} Orders
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Summary Card --}}
                    <div class="card border-0 shadow-sm" style="background: #f8f9fa; border-radius: 16px;">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Order Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="ps-0">Order ID:</td>
                                    <td class="text-end pe-0 fw-semibold">#{{ $order->id }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0">Order Date:</td>
                                    <td class="text-end pe-0">{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0">Order Time:</td>
                                    <td class="text-end pe-0">{{ $order->created_at->format('h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0">Last Updated:</td>
                                    <td class="text-end pe-0">{{ $order->updated_at->format('M d, Y h:i A') }}</td>
                                </tr>
                            </table>

                            @if($order->status === 'processing')
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Estimated Delivery:</small>
                                    <strong>{{ now()->addDays(5)->format('M d, Y') }} - {{ now()->addDays(7)->format('M d, Y') }}</strong>
                                </div>
                            @endif

                            @if($order->status === 'completed')
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Delivered on:</small>
                                    <strong>{{ $order->updated_at->format('M d, Y') }}</strong>
                                </div>
                            @endif

                            @if($order->status === 'cancelled')
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Cancelled on:</small>
                                    <strong>{{ $order->updated_at->format('M d, Y') }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            padding: 20px 0;
        }
        
        .timeline-steps::before {
            content: '';
            position: absolute;
            top: 50px;
            left: 60px;
            right: 60px;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .timeline-step {
            position: relative;
            z-index: 2;
            flex: 1;
            text-align: center;
        }
        
        .timeline-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            position: relative;
            z-index: 3;
            transition: all 0.3s;
        }
        
        .timeline-step.completed .timeline-icon {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .timeline-content {
            text-align: center;
        }
        
        .badge {
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 30px;
            padding: 8px 16px;
        }
        
        .card {
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
        }
        
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .btn-light {
            background: #f8f9fa;
            border-color: #f8f9fa;
        }
        
        .btn-light:hover {
            background: #e9ecef;
            border-color: #e9ecef;
        }
        
        .btn-success {
            background: #28a745;
            border-color: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
            border-color: #1e7e34;
        }
        
        .btn-primary {
            background: #007bff;
            border-color: #007bff;
        }
        
        .btn-primary:hover {
            background: #0069d9;
            border-color: #0062cc;
        }
        
        .status-icon {
            transition: all 0.3s;
        }
        
        .status-icon:hover {
            transform: scale(1.1) rotate(5deg);
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .status-processing .status-icon {
            animation: pulse 2s infinite;
        }
        
        @media (max-width: 768px) {
            .timeline-steps {
                flex-direction: column;
            }
            
            .timeline-steps::before {
                display: none;
            }
            
            .timeline-step {
                display: flex;
                text-align: left;
                margin-bottom: 20px;
            }
            
            .timeline-icon {
                margin: 0 20px 0 0;
            }
            
            .timeline-content {
                text-align: left;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
@endsection