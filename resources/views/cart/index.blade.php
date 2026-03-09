@extends('layouts.app')

@section('title', 'Shopping Cart - PageTurner')

@section('content')

<style>
    .btn-primary {
        background: #dc2626;
        border-color: #dc2626;
    }
    .btn-primary:hover {
        background: #b91c1c;
        border-color: #b91c1c;
    }
    .btn-primary:disabled {
        background: #dc2626;
        border-color: #dc2626;
        opacity: 0.65;
    }
    .btn-outline-danger {
        color: #dc2626;
        border-color: #dc2626;
    }
    .btn-outline-danger:hover {
        background: #dc2626;
        border-color: #dc2626;
    }
    .card {
        border-radius: 16px;
        overflow: hidden;
    }
    .list-group-item {
        transition: background-color 0.2s;
    }
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
    .badge {
        border-radius: 30px;
        font-weight: 500;
    }

    /* ── Cart item row ── */
    .cart-item {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f1f1;
    }
    .cart-item:last-child { border-bottom: none; }
    .cart-item-img {
        width: 64px;
        flex-shrink: 0;
    }
    .cart-item-img img {
        width: 64px;
        height: 88px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
    }
    .cart-item-info { flex: 1; min-width: 0; }
    .cart-item-info h6 {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cart-item-info span {
        font-size: 12px;
        color: #9ca3af;
    }
    .cart-item-price {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        width: 64px;
        text-align: right;
        flex-shrink: 0;
    }
    .cart-item-subtotal {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        width: 72px;
        text-align: right;
        flex-shrink: 0;
    }
    .cart-item-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    /* ── Quantity stepper ── */
    .qty-stepper {
        display: inline-flex;
        align-items: center;
        border: 1.5px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
    }
    .qty-stepper .qty-circle {
        width: 34px;
        height: 34px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-size: 16px;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s, color .15s;
        padding: 0;
        flex-shrink: 0;
        line-height: 1;
    }
    .qty-stepper .qty-circle:hover:not(:disabled) {
        background: #fef2f2;
        color: #dc2626;
    }
    .qty-stepper .qty-circle:disabled {
        opacity: .3;
        cursor: not-allowed;
    }
    .qty-stepper .qty-divider {
        width: 1px;
        height: 20px;
        background: #e5e7eb;
        flex-shrink: 0;
    }
    .qty-stepper .qty-num {
        width: 36px;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        user-select: none;
    }
    .qty-save {
        font-size: 11px;
        font-weight: 600;
        background: #dc2626;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 5px 12px;
        cursor: pointer;
        transition: background .15s;
        white-space: nowrap;
    }
    .qty-save:hover { background: #b91c1c; }
    .cart-remove-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1.5px solid #fee2e2;
        background: #fff;
        color: #dc2626;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s, border-color .15s;
        padding: 0;
        flex-shrink: 0;
    }
    .cart-remove-btn:hover {
        background: #fee2e2;
        border-color: #dc2626;
    }

    @media (max-width: 768px) {
        .list-group-item .text-md-end { text-align: left !important; }
    }
</style>

<section id="cart" class="py-5">
    <div class="container">

        {{-- Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="banner-title">Shopping Cart</h1>
                <p class="text-muted">Review and manage the items in your cart</p>
            </div>
        </div>

        @if(empty($cart))
            {{-- Empty Cart --}}
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1" class="mb-4">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <h3 class="mb-3">Your cart is empty</h3>
                            <p class="text-muted mb-4">Looks like you haven't added any books to your cart yet.</p>
                            <a href="{{ route('books.index') }}" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                                Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">

                {{-- Cart Items --}}
                <div class="col-lg-8 mb-4 mb-lg-0">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Cart Items ({{ $totalItems }})</h5>
                                <form action="{{ route('cart.clear') }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to clear your cart?')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        Clear Cart
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            {{-- Column headers --}}
                            <div class="d-flex align-items-center gap-3 px-4 py-2 border-bottom" style="background:#fafafa;">
                                <div style="width:64px; flex-shrink:0;"></div>
                                <div style="flex:1;" class="text-muted" style="font-size:11px;">Item</div>
                                <div style="width:64px; text-align:right; flex-shrink:0; font-size:11px;" class="text-muted">Price</div>
                                <div style="width:104px; text-align:center; flex-shrink:0; font-size:11px;" class="text-muted">Quantity</div>
                                <div style="width:72px; text-align:right; flex-shrink:0; font-size:11px;" class="text-muted">Subtotal</div>
                                <div style="width:32px; flex-shrink:0;"></div>
                            </div>
                            @foreach($cart as $bookId => $item)
                                @php
                                    $book     = App\Models\Book::find($bookId);
                                    $subtotal = $item['price'] * $item['quantity'];
                                @endphp
                                @if($book)
                                <div class="cart-item">

                                    {{-- Cover --}}
                                    <div class="cart-item-img">
                                        <img src="{{ empty($book->cover_image) ? asset('images/book_images/book-placeholder.png') : asset('storage/'.$book->cover_image) }}"
                                             alt="{{ $book->title }}">
                                    </div>

                                    {{-- Info --}}
                                    <div class="cart-item-info">
                                        <h6>{{ $book->title }}</h6>
                                        <span>by {{ $book->author }}</span>
                                        @if($book->stock_quantity < $item['quantity'])
                                            <div class="mt-1">
                                                <span class="badge bg-danger" style="font-size:10px;">Only {{ $book->stock_quantity }} left</span>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Unit price --}}
                                    <div class="cart-item-price">
                                        ${{ number_format($item['price'], 2) }}
                                    </div>

                                    {{-- Qty + update --}}
                                    <form action="{{ route('cart.update', $bookId) }}" method="POST" class="quantity-form d-flex flex-column align-items-center gap-1">
                                        @csrf
                                        @method('PATCH')
                                        <div class="qty-stepper">
                                            <button type="button" class="qty-circle qty-dec"
                                                {{ $item['quantity'] <= 1 ? 'disabled' : '' }}>−</button>
                                            <div class="qty-divider"></div>
                                            <span class="qty-num">{{ $item['quantity'] }}</span>
                                            <div class="qty-divider"></div>
                                            <input type="hidden" name="quantity"
                                                   value="{{ $item['quantity'] }}"
                                                   data-max="{{ $book->stock_quantity }}">
                                            <button type="button" class="qty-circle qty-inc"
                                                {{ $item['quantity'] >= $book->stock_quantity ? 'disabled' : '' }}>+</button>
                                        </div>
                                        <button type="submit" class="qty-save" style="display:none;">✓ Update</button>
                                    </form>

                                    {{-- Subtotal --}}
                                    <div class="cart-item-subtotal">
                                        ${{ number_format($subtotal, 2) }}
                                    </div>

                                    {{-- Remove --}}
                                    <form action="{{ route('cart.destroy', $bookId) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="cart-remove-btn"
                                            onclick="return confirm('Remove this item?')" title="Remove">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Order Summary --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td class="ps-0">Subtotal ({{ $totalItems }} items):</td>
                                    <td class="text-end pe-0">${{ number_format($totalAmount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0">Shipping:</td>
                                    <td class="text-end pe-0">Free</td>
                                </tr>
                                <tr class="border-top">
                                    <td class="ps-0 fw-bold">Total:</td>
                                    <td class="text-end pe-0 fw-bold">${{ number_format($totalAmount, 2) }}</td>
                                </tr>
                            </table>

                            @php
                                $outOfStock = false;
                                foreach($cart as $bookId => $item) {
                                    $b = App\Models\Book::find($bookId);
                                    if ($b && $b->stock_quantity < $item['quantity']) {
                                        $outOfStock = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if($outOfStock)
                                <div class="alert alert-warning small py-2">
                                    Some items exceed available stock. Please update quantities.
                                </div>
                            @endif

                            <form action="{{ route('orders.store') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"
                                    {{ $outOfStock ? 'disabled' : '' }}>
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                        <rect x="1" y="6" width="22" height="12" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    PROCEED TO CHECKOUT
                                </button>
                            </form>

                            <div class="text-center mt-3">
                                <a href="{{ route('books.index') }}" class="text-muted small">← Continue Shopping</a>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <p class="small text-muted mb-2">We Accept</p>
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                <span class="badge bg-white text-dark border p-2">Visa</span>
                                <span class="badge bg-white text-dark border p-2">Mastercard</span>
                                <span class="badge bg-white text-dark border p-2">PayPal</span>
                                <span class="badge bg-white text-dark border p-2">GCash</span>
                            </div>
                            <p class="small text-muted mb-0">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                    <rect x="1" y="6" width="22" height="12" rx="2" ry="2"></rect>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                Secure payment processing
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.quantity-form').forEach(function (form) {
        var decBtn  = form.querySelector('.qty-dec');
        var incBtn  = form.querySelector('.qty-inc');
        var numEl   = form.querySelector('.qty-num');
        var input   = form.querySelector('input[name="quantity"]');
        var saveBtn = form.querySelector('.qty-save');
        var max     = parseInt(input.getAttribute('data-max')) || 999;

        function refresh(val) {
            numEl.textContent   = val;
            input.value         = val;
            decBtn.disabled     = (val <= 1);
            incBtn.disabled     = (val >= max);
            saveBtn.style.display = 'inline-block';
        }

        decBtn.addEventListener('click', function () {
            var v = parseInt(input.value);
            if (v > 1) refresh(v - 1);
        });

        incBtn.addEventListener('click', function () {
            var v = parseInt(input.value);
            if (v < max) refresh(v + 1);
        });

        form.addEventListener('submit', function () {
            saveBtn.style.display = 'none';
        });
    });
});
</script>

@endsection