@extends('layouts.app')

@section('title', $order->id . ' - PageTurner')

@section('content')

    @php
        $orderItem = $order->orderItems[0];
        $book = $orderItem->book;

        // Status configuration
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'bg' => '#fff3cd',
                'border' => '#ffeeba',
                'text' => '#856404',
                'icon' => 'clock',
                'message' => 'Your order is pending confirmation. You can update the quantity while it\'s pending.'
            ],
            'processing' => [
                'color' => 'info',
                'bg' => '#d1ecf1',
                'border' => '#bee5eb',
                'text' => '#0c5460',
                'icon' => 'refresh-cw',
                'message' => 'Your order is being processed. Our team is preparing your items for shipment.'
            ],
            'completed' => [
                'color' => 'success',
                'bg' => '#d4edda',
                'border' => '#c3e6cb',
                'text' => '#155724',
                'icon' => 'check-circle',
                'message' => 'This order has been completed. Thank you for your purchase!'
            ],
            'cancelled' => [
                'color' => 'danger',
                'bg' => '#f8d7da',
                'border' => '#f5c6cb',
                'text' => '#721c24',
                'icon' => 'x-circle',
                'message' => 'This order has been cancelled. If you believe this is a mistake, please contact support.'
            ]
        ];

        $currentStatus = $statusConfig[$order->status] ?? $statusConfig['pending'];
    @endphp

    <section id="billboard" class="py-5">
        <div class="container">
            {{-- Order Status Banner with Dynamic Styling --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm"
                        style="border-left: 5px solid {{ $currentStatus['text'] }} !important;">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div class="d-flex align-items-center mb-2 mb-md-0">
                                    <div class="status-icon me-3"
                                        style="background: {{ $currentStatus['bg'] }}; padding: 12px; border-radius: 50%;">
                                        @if($currentStatus['icon'] == 'clock')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                        @elseif($currentStatus['icon'] == 'refresh-cw')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <path
                                                    d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15">
                                                </path>
                                            </svg>
                                        @elseif($currentStatus['icon'] == 'check-circle')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                            </svg>
                                        @elseif($currentStatus['icon'] == 'x-circle')
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="{{ $currentStatus['text'] }}" stroke-width="2">
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
                                    <small class="text-muted">{{ $orderItem->quantity }} item(s) •
                                        {{ $order->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>


                            {{-- Cancelled notice with reason (if you have one) --}}
                            @if($order->status == 'cancelled')
                                <div class="mt-3 p-2 rounded" style="background: {{ $currentStatus['bg'] }}40;">
                                    <small style="color: {{ $currentStatus['text'] }};">
                                        <strong>Reason:</strong> Cancelled at customer's request
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Main Content --}}
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm">
                        <div class="row g-0">
                            <div class="col-md-5 p-4">
                                <div class="position-relative">
                                    <img src="{{ empty($book->cover_image) ? asset('booksaw/images/main-banner1.jpg') : asset($book->cover_image) }}"
                                        alt="{{ $book->title }}" class="img-fluid rounded shadow-lg w-100">

                                    {{-- Status badge on image --}}
                                    <div class="position-absolute top-0 start-0 m-3">
                                        <span class="badge p-2"
                                            style="background: {{ $currentStatus['bg'] }}; color: {{ $currentStatus['text'] }};">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7 p-4">
                                <h2 class="banner-title mb-3">{{ $book->title }}</h2>
                                <h5 class="text-muted mb-3">by {{ $book->author }}</h5>

                                {{-- Stars --}}
                                <div class="d-flex align-items-center mb-3">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($book->average_rating))
                                            <svg style="width: 24px; height: 24px; color: #fbbf24;" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @else
                                            <svg style="width: 24px; height: 24px; color: #d1d5db;" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endif
                                    @endfor
                                    <span class="ms-2 text-muted">
                                        {{ number_format($book->average_rating, 1) }} ({{ $book->reviews->count() }}
                                        reviews)
                                    </span>
                                </div>

                                <p class="mb-4">{{ Str::limit($book->description, 200) }}</p>

                                <div class="border-top pt-3">
                                    <div class="row">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Price per item</small>
                                            <strong>${{ number_format($orderItem->unit_price, 2) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Subtotal</small>
                                            <strong>${{ number_format($orderItem->subtotal, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar with Actions --}}
                <div class="col-md-4">
                    {{-- Status Message Card --}}
                    <div class="card border-0 shadow-sm mb-4"
                        style="background: {{ $currentStatus['bg'] }}; border-color: {{ $currentStatus['border'] }};">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="me-3">
                                    @if($currentStatus['icon'] == 'clock')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    @elseif($currentStatus['icon'] == 'refresh-cw')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <path
                                                d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15">
                                            </path>
                                        </svg>
                                    @elseif($currentStatus['icon'] == 'check-circle')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="{{ $currentStatus['text'] }}" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    @elseif($currentStatus['icon'] == 'x-circle')
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="{{ $currentStatus['text'] }}" stroke-width="2">
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
                                    <p
                                        style="color: {{ $currentStatus['text'] }}; opacity: 0.9; margin-bottom: 0; font-size: 0.9rem;">
                                        {{ $currentStatus['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Order Actions Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Order Actions</h5>
                        </div>
                        <div class="card-body">
                            @auth
                                @if(!auth()->user()->isAdmin())
                                    {{-- Show different actions based on status --}}
                                    @if($order->status == 'pending')
                                        {{-- Update Form for Pending Orders --}}
                                        <div class="mb-4">
                                            <label class="form-label fw-bold mb-3">Update Quantity</label>
                                            <form action="{{ route('orders.update', $order->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')

                                                <div class="mb-3">
                                                    <label class="form-label text-muted small">Current Quantity:
                                                        {{ $order->orderItems[0]->quantity }}</label>
                                                    <input type="number" name="quantity" class="form-control" min="1"
                                                        max="{{ $book->stock_quantity + $order->orderItems[0]->quantity }}"
                                                        value="{{ $order->orderItems[0]->quantity }}">
                                                    <small class="text-muted">Available stock: {{ $book->stock_quantity }}</small>
                                                </div>

                                                <input type="hidden" name="previous_quantity"
                                                    value="{{ $order->orderItems[0]->quantity }}">
                                                <input type="hidden" name="order_item_id" value="{{ $order->orderItems[0]->id }}">

                                                <button type="submit" class="btn btn-primary w-100 mb-3">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" class="me-2">
                                                        <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34">
                                                        </path>
                                                        <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"></polygon>
                                                    </svg>
                                                    Update Order
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($order->status == 'processing')
                                        {{-- Processing Actions --}}
                                        <div class="text-center py-3">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0c5460"
                                                stroke-width="1.5" class="mb-3">
                                                <path
                                                    d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15">
                                                </path>
                                            </svg>
                                            <h6 style="color: #0c5460;">Order Being Processed</h6>
                                            <p class="text-muted small">Your order is being prepared. You cannot modify it at this
                                                stage.</p>
                                        </div>
                                    @elseif($order->status == 'cancelled')
                                        {{-- Cancelled Actions --}}
                                        <div class="text-center py-3">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#721c24"
                                                stroke-width="1.5" class="mb-3">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                            <h6 style="color: #721c24;">Order Cancelled</h6>
                                            <p class="text-muted small">This order has been cancelled. Would you like to reorder?</p>
                                            <a href="{{ route('books.show', $book) }}" class="btn btn-outline-danger btn-sm">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" class="me-1">
                                                    <path d="M3 1v7h7M21 17v7h-7M4 20l16-16M20 4L4 20"></path>
                                                </svg>
                                                Reorder Item
                                            </a>
                                        </div>
                                    @endif

                                    {{-- Delete Form (Available for pending only) --}}
                                    @if($order->status == 'pending')
                                        <div class="border-top pt-3">
                                            <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger w-100"
                                                    onclick="return confirm('Are you sure you want to delete this order? This action cannot be undone.')">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" class="me-2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path
                                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                        </path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
                                                    Delete Order
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($order->status == 'cancelled')
                                        {{-- Option to remove cancelled order --}}
                                        <div class="border-top pt-3">
                                            <form action=" {{ route('orders.destroy', $order->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-secondary w-100"
                                                    onclick="return confirm('Remove this cancelled order from your history?')">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" class="me-2">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path
                                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                        </path>
                                                    </svg>
                                                    Remove from History
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($order->status == 'completed')
                                        {{-- leaving review option --}}
                                        <div class="border-top pt-3">
                                            <a href={{ route('books.show', $book->id) }}>
                                                <button class="btn btn-primary w-100 mb-3">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" class="me-1">
                                                        <path d="M12 2L15 9H22L16 14L19 21L12 16.5L5 21L8 14L2 9H9L12 2Z" />
                                                    </svg>
                                                    Leave a Review
                                                </button>
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            @endauth
                        </div>
                    </div>

                    {{-- Order Summary Card --}}
                    <div class="card border-0 shadow-sm" style="background: #f8f9fa;">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Order Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="ps-0">Subtotal:</td>
                                    <td class="text-end pe-0">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0">Shipping:</td>
                                    <td class="text-end pe-0">Free</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="ps-0 fw-bold">Total:</td>
                                    <td class="text-end pe-0 fw-bold">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </table>

                            {{-- Estimated delivery for processing --}}
                            @if($order->status == 'processing')
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Estimated Delivery:</small>
                                    <strong>{{ now()->addDays(5)->format('M d, Y') }} -
                                        {{ now()->addDays(7)->format('M d, Y') }}</strong>
                                </div>
                            @endif

                            {{-- Completed date --}}
                            @if($order->status == 'completed')
                                <div class="mt-2 pt-2 border-top">
                                    <small class="text-muted d-block">Delivered on:</small>
                                    <strong>{{ $order->updated_at->format('M d, Y') }}</strong>
                                </div>
                            @endif

                            {{-- Cancelled date --}}
                            @if($order->status == 'cancelled')
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
    </section>

@endsection

@section('styles')
    <style>
        .badge {
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 30px;
        }

        .card {
            border-radius: 16px;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-2px);
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

        .form-control {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
            border-color: #dc2626;
            transform: translateY(-1px);
        }

        .btn-primary {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-primary:hover {
            background: #b91c1c;
            border-color: #b91c1c;
        }

        .btn-outline-danger {
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-outline-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }

        .btn-outline-info {
            color: #0c5460;
            border-color: #0c5460;
        }

        .btn-outline-info:hover {
            background: #0c5460;
            border-color: #0c5460;
            color: white;
        }

        .progress {
            border-radius: 10px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 10px;
            transition: width 0.6s ease;
        }

        .status-icon {
            transition: all 0.3s;
        }

        .status-icon:hover {
            transform: scale(1.1) rotate(5deg);
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }

        .status-processing .status-icon {
            animation: pulse 2s infinite;
        }
    </style>
@endsection