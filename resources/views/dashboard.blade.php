@extends('layouts.app')

@section('title', 'My Dashboard - PageTurner')

@section('content')
<div class="container py-5">

    {{-- Welcome Banner --}}
    <div class="card border-0 shadow-lg mb-4"
         style="border-radius: 20px; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%); overflow: hidden; position: relative;">
        <div style="position:absolute;right:2rem;top:-1rem;font-size:10rem;font-family:Georgia,serif;color:rgba(220,38,38,0.07);line-height:1;pointer-events:none;">"</div>
        <div class="card-body p-4 p-md-5">
            <h3 class="fw-bold text-white mb-1">
                Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }} 👋
            </h3>
            <p class="mb-3" style="color:rgba(255,255,255,0.6);font-size:0.9rem;">Here's a look at your reading journey and account activity.</p>
            <div class="d-flex flex-wrap gap-2">
                @if(auth()->user()->hasVerifiedEmail())
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(25,135,84,0.3);color:#6fcf97;border:1px solid rgba(111,207,151,0.3);font-weight:500;"><i class="bi bi-check-circle-fill me-1"></i>Email Verified</span>
                @else
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(220,38,38,0.3);color:#f1948a;border:1px solid rgba(241,148,138,0.3);font-weight:500;"><i class="bi bi-exclamation-circle-fill me-1"></i>Email Not Verified</span>
                @endif
                @if(auth()->user()->two_factor_enabled)
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(13,110,253,0.3);color:#7fc0f5;border:1px solid rgba(127,192,245,0.3);font-weight:500;"><i class="bi bi-shield-fill-check me-1"></i>2FA Active ({{ strtoupper(auth()->user()->two_factor_type) }})</span>
                @else
                    <span class="badge rounded-pill px-3 py-2" style="background:rgba(108,117,125,0.25);color:#adb5bd;border:1px solid rgba(173,181,189,0.2);font-weight:500;"><i class="bi bi-shield-x me-1"></i>2FA Disabled</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Order Stat Cards --}}
    <div class="row g-3 mb-4">
        @php
            $statCards = [
                ['label'=>'Total Orders',  'value'=>$orderSummary['total'],      'icon'=>'bi-bag-fill',          'color'=>'#dc2626', 'bg'=>'rgba(220,38,38,0.08)'],
                ['label'=>'Pending',       'value'=>$orderSummary['pending'],    'icon'=>'bi-clock-fill',        'color'=>'#f97316', 'bg'=>'rgba(249,115,22,0.08)'],
                ['label'=>'Processing',    'value'=>$orderSummary['processing'], 'icon'=>'bi-arrow-repeat',      'color'=>'#0d6efd', 'bg'=>'rgba(13,110,253,0.08)'],
                ['label'=>'Completed',     'value'=>$orderSummary['completed'],  'icon'=>'bi-check-circle-fill', 'color'=>'#198754', 'bg'=>'rgba(25,135,84,0.08)'],
            ];
        @endphp
        @foreach($statCards as $stat)
        <div class="col-6 col-md-3">
            <a href="{{ route('orders.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-lg h-100" style="border-radius:20px;background:linear-gradient(145deg,#ffffff,#f8f9fa);transition:transform 0.2s,box-shadow 0.2s;"
                     onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 20px 40px rgba(0,0,0,0.12)';"
                     onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='';">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:48px;height:48px;background:{{ $stat['bg'] }};">
                                <i class="bi {{ $stat['icon'] }}" style="font-size:1.3rem;color:{{ $stat['color'] }};"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:1.6rem;line-height:1;color:#1e293b;">{{ $stat['value'] }}</div>
                                <div class="text-muted" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;">{{ $stat['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    {{-- Main Grid --}}
    <div class="row g-4">

        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Recent Orders --}}
            <div class="card border-0 shadow-lg mb-4" style="border-radius:20px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0" style="color:#1e293b;letter-spacing:0.5px;"><i class="bi bi-bag me-2 text-danger"></i>RECENT ORDERS</h6>
                    <a href="{{ route('orders.index') }}" class="text-danger fw-bold small text-decoration-none">View All →</a>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    @forelse($recentOrders as $order)
                        @php
                            $badgeMap = [
                                'pending'    => ['bg-warning bg-opacity-10 text-warning',  'bi-clock'],
                                'processing' => ['bg-primary bg-opacity-10 text-primary',  'bi-arrow-repeat'],
                                'completed'  => ['bg-success bg-opacity-10 text-success',  'bi-check-circle'],
                                'cancelled'  => ['bg-danger bg-opacity-10 text-danger',    'bi-x-circle'],
                            ];
                            [$badgeCls, $badgeIcon] = $badgeMap[$order->status] ?? ['bg-secondary bg-opacity-10 text-secondary','bi-circle'];
                            $firstBook = $order->orderItems->first()?->book;
                        @endphp
                        <a href="{{ route('orders.show', $order->id) }}"
                           class="d-flex align-items-center gap-3 p-3 rounded-4 mb-2 text-decoration-none"
                           style="background:rgba(220,38,38,0.03);border:1px solid rgba(220,38,38,0.08);transition:all 0.2s;"
                           onmouseover="this.style.background='rgba(220,38,38,0.07)';this.style.borderColor='rgba(220,38,38,0.2)';"
                           onmouseout="this.style.background='rgba(220,38,38,0.03)';this.style.borderColor='rgba(220,38,38,0.08)';">
                            <div class="flex-shrink-0">
                                @if($firstBook?->cover_image)
                                    <img src="{{ asset('storage/' . $firstBook->cover_image) }}" alt=""
                                         style="width:40px;height:54px;border-radius:6px;object-fit:cover;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                                @else
                                    <div class="d-flex align-items-center justify-content-center rounded-3"
                                         style="width:40px;height:54px;background:linear-gradient(145deg,#f5efe6,#e8d5b7);">
                                        <i class="bi bi-book" style="color:#dc2626;font-size:1rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate" style="color:#1e293b;font-size:0.875rem;">
                                    {{ $firstBook?->title ?? 'Order #'.$order->id }}
                                    @if($order->orderItems->count() > 1)
                                        <span class="text-muted fw-normal"> +{{ $order->orderItems->count()-1 }} more</span>
                                    @endif
                                </div>
                                <small class="text-muted">#{{ $order->id }} · {{ $order->created_at->format('M d, Y') }}</small>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="fw-bold" style="color:#1e293b;font-size:0.875rem;">₱{{ number_format($order->total_amount,2) }}</div>
                                <span class="badge rounded-pill {{ $badgeCls }} mt-1" style="font-size:0.68rem;"><i class="bi {{ $badgeIcon }} me-1"></i>{{ ucfirst($order->status) }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4">
                            <div class="mb-3" style="width:64px;height:64px;background:rgba(220,38,38,0.05);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                <i class="bi bi-inbox text-danger" style="font-size:1.8rem;opacity:0.5;"></i>
                            </div>
                            <p class="text-muted small mb-3">No orders yet.</p>
                            <a href="{{ route('books.index') }}" class="btn btn-danger btn-sm rounded-pill px-4" style="background:linear-gradient(135deg,#dc2626,#ef4444);border:none;">Browse Books</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Explore Books --}}
            <div class="card border-0 shadow-lg mb-4" style="border-radius:20px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0" style="color:#1e293b;letter-spacing:0.5px;"><i class="bi bi-book-half me-2 text-danger"></i>EXPLORE BOOKS</h6>
                    <a href="{{ route('books.index') }}" class="text-danger fw-bold small text-decoration-none">See All →</a>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        @foreach($featuredBooks as $book)
                        <div class="col-md-3">
                            <div class="product-item">
                                <figure class="product-style">
                                    <img src="{{ empty($book->cover_image) ? asset('images/book_images/book-placeholder.png') : asset('storage/' . $book->cover_image) }}"
                                        alt="{{ $book->title }}" class="product-item">
                                    <a href="{{ route('books.show', $book) }}">
                                        <button type="button" class="add-to-cart" data-product-tile="add-to-cart">View Details</button>
                                    </a>
                                </figure>
                                <figcaption>
                                    <h3>{{ $book->title }}</h3>
                                    <span>{{ $book->author }}</span>
                                    <div class="item-price">${{ $book->price }}</div>
                                    <div style="display:flex;align-items:center;justify-content:center;gap:2px;margin-bottom:8px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= round($book->average_rating))
                                                <svg style="width:14px;height:14px;color:#fbbf24;" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @else
                                                <svg style="width:14px;height:14px;color:#d1d5db;" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endif
                                        @endfor
                                        <span style="margin-left:4px;font-size:11px;color:#6b7280;">({{ $book->reviews->count() }})</span>
                                    </div>
                                </figcaption>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- My Reviews --}}
            <div class="card border-0 shadow-lg" style="border-radius:20px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0" style="color:#1e293b;letter-spacing:0.5px;"><i class="bi bi-star me-2 text-danger"></i>MY REVIEWS</h6>
                </div>
                <div class="card-body px-4 pb-4 pt-3">
                    @forelse($recentReviews as $review)
                        <div class="d-flex gap-3 p-3 rounded-4 mb-2" style="background:rgba(220,38,38,0.03);border:1px solid rgba(220,38,38,0.08);">
                            @if($review->book?->cover_image)
                                <img src="{{ asset('storage/'.$review->book->cover_image) }}" alt=""
                                     style="width:36px;height:50px;border-radius:6px;object-fit:cover;flex-shrink:0;box-shadow:0 2px 8px rgba(0,0,0,0.12);">
                            @else
                                <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                     style="width:36px;height:50px;background:linear-gradient(145deg,#f5efe6,#e8d5b7);">
                                    <i class="bi bi-book" style="color:#dc2626;font-size:0.85rem;"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-truncate" style="font-size:0.85rem;color:#1e293b;">
                                    <a href="{{ route('books.show', $review->book) }}" class="text-decoration-none" style="color:#1e293b;">{{ $review->book->title }}</a>
                                </div>
                                <div class="my-1">
                                    @for($i=1;$i<=5;$i++)
                                        <i class="bi bi-star-fill" style="font-size:0.7rem;color:{{ $i<=$review->rating ? '#fbbf24' : '#e5e7eb' }};"></i>
                                    @endfor
                                    <small class="text-muted ms-1">{{ $review->created_at->format('M d, Y') }}</small>
                                </div>
                                @if($review->comment)
                                    <div class="text-muted text-truncate" style="font-size:0.8rem;">{{ $review->comment }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <div class="mb-3" style="width:64px;height:64px;background:rgba(220,38,38,0.05);border-radius:20px;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                                <i class="bi bi-chat-square-text text-danger" style="font-size:1.8rem;opacity:0.5;"></i>
                            </div>
                            <p class="text-muted small mb-3">No reviews written yet.</p>
                            <a href="{{ route('books.index') }}" class="btn btn-outline-danger btn-sm rounded-pill px-4">Find Books to Review</a>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">

            {{-- Quick Links --}}
            <div class="card border-0 shadow-lg mb-4" style="border-radius:20px;background:linear-gradient(145deg,#ffffff,#f8f9fa);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="color:#1e293b;letter-spacing:0.5px;">QUICK LINKS</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('books.index') }}"            class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-book me-2"></i>Browse Books</a>
                        <a href="{{ route('orders.index') }}"           class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-package me-2"></i>Order History</a>
                        <a href="{{ route('cart.index') }}"             class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-cart me-2"></i>My Cart</a>
                        <a href="{{ route('profile.edit') }}"           class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-person me-2"></i>Edit Profile</a>
                        <a href="{{ route('profile.edit') }}#security"  class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-shield-lock me-2"></i>Security Settings</a>
                    </div>
                </div>
            </div>

            {{-- Account Status --}}
            <div class="card border-0 shadow-lg mb-4" style="border-radius:20px;background:linear-gradient(145deg,#ffffff,#f8f9fa);">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="color:#1e293b;letter-spacing:0.5px;">ACCOUNT STATUS</h6>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:rgba(220,38,38,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-envelope-fill text-danger" style="font-size:0.85rem;"></i>
                                </div>
                                <small class="fw-semibold" style="color:#1e293b;">Email</small>
                            </div>
                            @if(auth()->user()->hasVerifiedEmail())
                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3">Verified</span>
                            @else
                                <a href="{{ route('verification.notice') }}" class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 text-decoration-none">Verify Now</a>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:rgba(220,38,38,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-shield-fill text-danger" style="font-size:0.85rem;"></i>
                                </div>
                                <small class="fw-semibold" style="color:#1e293b;">Two-Factor</small>
                            </div>
                            @if(auth()->user()->two_factor_enabled)
                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3">{{ strtoupper(auth()->user()->two_factor_type) }}</span>
                            @else
                                <a href="{{ route('profile.edit') }}#security" class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 text-decoration-none">Enable</a>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:rgba(220,38,38,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-calendar3 text-danger" style="font-size:0.85rem;"></i>
                                </div>
                                <small class="fw-semibold" style="color:#1e293b;">Member Since</small>
                            </div>
                            <small class="text-muted fw-semibold">{{ auth()->user()->created_at->format('M Y') }}</small>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width:32px;height:32px;background:rgba(220,38,38,0.08);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                                    <i class="bi bi-award-fill text-danger" style="font-size:0.85rem;"></i>
                                </div>
                                <small class="fw-semibold" style="color:#1e293b;">Account Type</small>
                            </div>
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3">{{ ucfirst(auth()->user()->role ?? 'Customer') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Categories --}}
            {{-- <div class="card border-0 shadow-lg" style="border-radius:20px;background:linear-gradient(145deg,#ffffff,#f8f9fa);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0" style="color:#1e293b;letter-spacing:0.5px;">CATEGORIES</h6>
                        
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($categories->take(12) as $cat)
                            <span class="badge rounded-pill fw-normal px-3 py-2"
                               style="background:rgba(220,38,38,0.06);color:#1e293b;border:1px solid rgba(220,38,38,0.15);font-size:0.78rem;">
                                {{ $cat->name }} <span style="opacity:0.6;">({{ $cat->books_count }})</span>
                            </span>
                        @empty
                            <p class="text-muted small mb-0">No categories yet.</p>
                        @endforelse
                    </div>
                </div>
            </div> --}}

        </div>
    </div>
</div>
@endsection