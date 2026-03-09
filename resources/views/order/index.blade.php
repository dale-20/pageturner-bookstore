@extends('layouts.app')

@section('title', 'My Orders - PageTurner')

@section('content')
    <div class="container mt-4">
        {{-- Header with Stats --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div>
                        <h1 class="banner-title mb-1">My Orders</h1>
                        <p class="text-muted">Track and manage your book purchases</p>
                    </div>
                    <div class="d-flex gap-3">
                        <div class="text-center px-3 py-2 bg-light rounded-3">
                            <span class="d-block fw-bold text-primary">{{ $totalOrders }}</span>
                            <small class="text-muted">Total Orders</small>
                        </div>
                        <div class="text-center px-3 py-2 bg-light rounded-3">
                            <span class="d-block fw-bold text-success">${{ number_format($totalSpent, 2) }}</span>
                            <small class="text-muted">Total Spent</small>
                        </div>
                        <div class="text-center px-3 py-2 bg-light rounded-3">
                            <span class="d-block fw-bold text-info">{{ $recentOrders }}</span>
                            <small class="text-muted">Last 30 Days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status Filter Tabs --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-3">
                        <ul class="nav nav-pills" id="orderTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ !request('status') ? 'active' : '' }}" 
                                    id="all-tab" data-bs-toggle="pill" data-bs-target="#all" 
                                    type="button" role="tab" aria-controls="all" aria-selected="true"
                                    onclick="window.location='{{ route('orders.index') }}'">
                                    All Orders
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary ms-2">{{ $totalOrders }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ request('status') == 'pending' ? 'active' : '' }}" 
                                    id="pending-tab" data-bs-toggle="pill" data-bs-target="#pending" 
                                    type="button" role="tab" aria-controls="pending" aria-selected="false"
                                    onclick="window.location='{{ route('orders.index', ['status' => 'pending']) }}'">
                                    Pending
                                    @if($statusCounts['pending'] > 0)
                                        <span class="badge bg-warning bg-opacity-10 text-warning ms-2">{{ $statusCounts['pending'] }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ request('status') == 'processing' ? 'active' : '' }}" 
                                    id="processing-tab" data-bs-toggle="pill" data-bs-target="#processing" 
                                    type="button" role="tab" aria-controls="processing" aria-selected="false"
                                    onclick="window.location='{{ route('orders.index', ['status' => 'processing']) }}'">
                                    Processing
                                    @if($statusCounts['processing'] > 0)
                                        <span class="badge bg-info bg-opacity-10 text-info ms-2">{{ $statusCounts['processing'] }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}" 
                                    id="completed-tab" data-bs-toggle="pill" data-bs-target="#completed" 
                                    type="button" role="tab" aria-controls="completed" aria-selected="false"
                                    onclick="window.location='{{ route('orders.index', ['status' => 'completed']) }}'">
                                    Completed
                                    @if($statusCounts['completed'] > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success ms-2">{{ $statusCounts['completed'] }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ request('status') == 'cancelled' ? 'active' : '' }}" 
                                    id="cancelled-tab" data-bs-toggle="pill" data-bs-target="#cancelled" 
                                    type="button" role="tab" aria-controls="cancelled" aria-selected="false"
                                    onclick="window.location='{{ route('orders.index', ['status' => 'cancelled']) }}'">
                                    Cancelled
                                    @if($statusCounts['cancelled'] > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger ms-2">{{ $statusCounts['cancelled'] }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Orders List --}}
        <div class="row">
            <div class="col-12">
                @forelse($orders as $order)
                    @php
                        $statusColors = [
                            'pending' => ['bg' => '#fff3cd', 'text' => '#856404', 'dot' => '#ffc107', 'border' => '#ffeeba'],
                            'processing' => ['bg' => '#d1ecf1', 'text' => '#0c5460', 'dot' => '#17a2b8', 'border' => '#bee5eb'],
                            'completed' => ['bg' => '#d4edda', 'text' => '#155724', 'dot' => '#28a745', 'border' => '#c3e6cb'],
                            'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24', 'dot' => '#dc3545', 'border' => '#f5c6cb']
                        ];
                        $statusStyle = $statusColors[$order->status] ?? $statusColors['pending'];
                        
                        // Calculate order stats
                        $itemCount = $order->orderItems->count();
                        $uniqueBooks = $order->orderItems->pluck('book_id')->unique()->count();
                    @endphp

                    <div class="card border-0 shadow-sm mb-4 order-card" data-status="{{ $order->status }}">
                        {{-- Order Header --}}
                        <div class="card-header bg-white py-3" style="border-bottom: 2px solid {{ $statusStyle['border'] }};">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5">
                                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">Order #{{ $order->id }}</h5>
                                            <div class="d-flex align-items-center small text-muted">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                {{ $order->created_at->format('F d, Y \a\t h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="d-flex gap-3">
                                        <div class="text-center px-3 py-2 bg-light rounded-3">
                                            <span class="d-block fw-bold">{{ $itemCount }}</span>
                                            <small class="text-muted">Items</small>
                                        </div>
                                        <div class="text-center px-3 py-2 bg-light rounded-3">
                                            <span class="d-block fw-bold">{{ $uniqueBooks }}</span>
                                            <small class="text-muted">Books</small>
                                        </div>
                                        <div class="text-center px-3 py-2 bg-light rounded-3 flex-grow-1">
                                            <span class="d-block fw-bold text-success">${{ number_format($order->total_amount, 2) }}</span>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        <div class="d-flex align-items-center me-2">
                                            <span class="status-dot me-2" style="width: 8px; height: 8px; background: {{ $statusStyle['dot'] }}; border-radius: 50%; display: inline-block;"></span>
                                            <span class="badge px-3 py-2" style="background: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['text'] }};">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </div>
                                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm" style="background: #dc2626; color: white; border-radius: 20px; padding: 8px 20px;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                                <circle cx="12" cy="8" r="1" fill="currentColor"></circle>
                                            </svg>
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Order Items Preview --}}
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach($order->orderItems->take(3) as $orderItem)
                                    @php
                                        $book = $orderItem->book;
                                    @endphp
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                            <div class="flex-shrink-0">
                                                <img src="{{ empty($book->cover_image) ? asset('images/book_images/book-placeholder.png') : asset('storage/'. $book->cover_image) }}"
                                                    alt="{{ $book->title }}" 
                                                    class="rounded" 
                                                    style="width: 50px; height: 70px; object-fit: cover;">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1 text-truncate" style="max-width: 150px;">{{ $book->title }}</h6>
                                                <p class="text-muted small mb-1">by {{ $book->author }}</p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="small">
                                                        <strong>{{ $orderItem->quantity }}</strong> × ${{ number_format($orderItem->unit_price, 2) }}
                                                    </span>
                                                    <span class="fw-bold text-primary small">
                                                        ${{ number_format($orderItem->subtotal, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($order->orderItems->count() > 3)
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center justify-content-center p-3 bg-light rounded-3 h-100">
                                            <div class="text-center">
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary p-3">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mb-1">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                                        <line x1="12" y1="8" x2="12" y2="16"></line>
                                                    </svg>
                                                    <span class="d-block mt-1">+{{ $order->orderItems->count() - 3 }} more items</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Order Footer with Action Buttons --}}
                        @if($order->status == 'pending')
                            <div class="card-footer bg-white py-3 border-top">
                                <div class="d-flex justify-content-end gap-2">
                                    <form action="{{ route('order.cancel', $order) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" 
                                            onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                            </svg>
                                            Cancel Order
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    {{-- Empty State --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1" class="mb-4">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            <h3 class="mb-3">No orders yet</h3>
                            <p class="text-muted mb-4">Looks like you haven't placed any orders. Start shopping to see your orders here!</p>
                            <a href="{{ route('books.index') }}" class="btn btn-primary px-5 py-3" style="background: #dc2626; border-color: #dc2626;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                Browse Books
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pagination --}}
        @if($orders->hasPages())
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                        </div>
                        <div>
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('styles')
    <style>
        .banner-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .order-card {
            transition: all 0.3s;
            border-radius: 16px;
            overflow: hidden;
        }

        .order-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            transition: background-color 0.2s;
        }

        .nav-pills .nav-link {
            color: #6c757d;
            border-radius: 30px;
            padding: 8px 20px;
            margin-right: 8px;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
        }

        .nav-pills .nav-link:hover {
            background: #fef2f2;
            color: #dc2626;
        }

        .nav-pills .nav-link.active {
            background: #dc2626;
            color: white;
        }

        .nav-pills .nav-link .badge {
            font-weight: normal;
            padding: 4px 8px;
        }

        .btn-primary {
            background: #dc2626;
            border-color: #dc2626;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: #b91c1c;
            border-color: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }

        .btn-outline-danger {
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-outline-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }

        .badge {
            font-weight: 500;
            border-radius: 30px;
            padding: 8px 12px;
        }

        .status-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .rounded-3 {
            border-radius: 12px !important;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Pagination styling */
        .pagination {
            gap: 5px;
        }

        .pagination .page-link {
            border-radius: 8px;
            color: #dc2626;
            border: 1px solid #e5e7eb;
            padding: 8px 14px;
            transition: all 0.2s;
        }

        .pagination .page-link:hover {
            background: #dc2626;
            color: white;
            border-color: #dc2626;
            transform: translateY(-2px);
        }

        .pagination .active .page-link {
            background: #dc2626;
            border-color: #dc2626;
            color: white;
            font-weight: 600;
        }

        /* Loading state */
        .filter-loading {
            position: relative;
            pointer-events: none;
        }
        
        .filter-loading::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .filter-loading::before {
            content: '';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #dc2626;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 10000;
        }
        
        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .banner-title {
                font-size: 2rem;
            }
            
            .card-header .row > div {
                margin-bottom: 15px;
            }
            
            .card-header .d-flex.justify-content-end {
                justify-content: flex-start !important;
            }
            
            .nav-pills {
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 10px;
            }
            
            .nav-pills .nav-link {
                white-space: nowrap;
            }
        }
    </style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add loading state to filter tabs
        const filterTabs = document.querySelectorAll('#orderTabs .nav-link');
        
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                if (!this.classList.contains('active')) {
                    document.body.classList.add('filter-loading');
                }
            });
        });
        
        // Preserve active tab based on URL
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        
        if (status) {
            const activeTab = document.getElementById(status + '-tab');
            if (activeTab) {
                // Remove active class from all tabs
                filterTabs.forEach(t => t.classList.remove('active'));
                // Add active class to current tab
                activeTab.classList.add('active');
            }
        }
        
        // Remove loading state when page is fully loaded
        window.addEventListener('load', function() {
            document.body.classList.remove('filter-loading');
        });
    });
</script>
@endsection