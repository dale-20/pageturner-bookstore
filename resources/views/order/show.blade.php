@extends('layouts.app')

@section('title', 'Order #' . $order->id . ' - PageTurner')

@section('content')

    @php
        // Status configuration
        $statusConfig = [
            'pending' => [
                'color' => 'warning',
                'bg' => '#fff3cd',
                'border' => '#ffeeba',
                'text' => '#856404',
                'icon' => 'clock',
                'message' => 'Your order is pending confirmation. You can update quantities while it\'s pending.'
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
                                    <small class="text-muted">{{ $order->orderItems->sum('quantity') }} item(s) •
                                        {{ $order->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Main Content - Order Items List --}}
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Order Items ({{ $order->orderItems->count() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($order->orderItems as $index => $orderItem)
                                    @php
                                        $book = $orderItem->book;
                                    @endphp
                                    <div class="list-group-item p-4">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <img src="{{ empty($book->cover_image) ? asset('images/book_images/book-placeholder.png') : asset('storage/' . $book->cover_image) }}"
                                                    alt="{{ $book->title }}" class="img-fluid rounded shadow-sm">
                                            </div>
                                            <div class="col-md-9">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h5 class="mb-1">{{ $book->title }}</h5>
                                                        <p class="text-muted mb-2">by {{ $book->author }}</p>

                                                        {{-- Stars --}}
                                                        <div class="d-flex align-items-center mb-2">
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= round($book->average_rating))
                                                                    <svg style="width: 16px; height: 16px; color: #fbbf24;"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path
                                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                    </svg>
                                                                @else
                                                                    <svg style="width: 16px; height: 16px; color: #d1d5db;"
                                                                        fill="currentColor" viewBox="0 0 20 20">
                                                                        <path
                                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                                    </svg>
                                                                @endif
                                                            @endfor
                                                            <span class="ms-2 small text-muted">
                                                                {{ number_format($book->average_rating, 1) }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    {{-- Status badge per item (optional) --}}
                                                    <span class="badge p-2"
                                                        style="background: {{ $currentStatus['bg'] }}; color: {{ $currentStatus['text'] }};">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block">Price</small>
                                                        <strong>${{ number_format($orderItem->unit_price, 2) }}</strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block">Quantity</small>
                                                        <strong>{{ $orderItem->quantity }}</strong>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <small class="text-muted d-block">Subtotal</small>
                                                        <strong>${{ number_format($orderItem->subtotal, 2) }}</strong>
                                                    </div>
                                                </div>

                                                {{-- Review button for completed orders --}}
                                                @if($order->status == 'completed' && auth()->check() && !auth()->user()->isAdmin())
                                                    <div class="mt-3 pt-3 border-top">
                                                        <a href="{{ route('books.show', $book->id) }}"
                                                            class="btn btn-outline-primary btn-sm">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                                stroke="currentColor" stroke-width="2" class="me-1">
                                                                <path d="M12 2L15 9H22L16 14L19 21L12 16.5L5 21L8 14L2 9H9L12 2Z" />
                                                            </svg>
                                                            Leave a Review
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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

                    {{-- Order Summary Card --}}
                    <div class="card border-0 shadow-sm mb-4" style="background: #f8f9fa;">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
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

                            {{-- Items count --}}
                            <div class="mt-2 pt-2 border-top">
                                <small class="text-muted d-block">Total Items:</small>
                                <strong>{{ $order->orderItems->sum('quantity') }}</strong>
                            </div>

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

                    {{-- Order Actions Card --}}
                    @auth
                        @if(!auth()->user()->isAdmin())
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white py-3">
                                    <h5 class="mb-0">Order Actions</h5>
                                </div>
                                <div class="card-body">
                                    @if($order->status == 'pending')
                                        {{-- Cancel/Delete Order --}}
                                        <form action="{{ route('order.cancel', $order) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-outline-danger w-100"
                                                onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.')">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" class="me-2">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path
                                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                    </path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                                Cancel Entire Order
                                            </button>
                                        </form>
                                    @elseif($order->status == 'cancelled')
                                        {{-- Option to remove cancelled order from history --}}
                                        <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
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
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endauth
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

        .list-group-item {
            transition: background-color 0.2s;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
        }

        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-sm {
            padding: 5px 15px;
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

        .form-control-sm {
            padding: 5px 10px;
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

        .btn-outline-primary {
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-outline-primary:hover {
            background: #dc2626;
            border-color: #dc2626;
            color: white;
        }

        .btn-outline-danger {
            color: #dc2626;
            border-color: #dc2626;
        }

        .btn-outline-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
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

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .list-group-item .row>div {
                margin-bottom: 15px;
            }

            .list-group-item .col-md-3 {
                text-align: center;
            }

            .list-group-item img {
                max-width: 150px;
                margin-bottom: 10px;
            }
        }
    </style>
@endsection