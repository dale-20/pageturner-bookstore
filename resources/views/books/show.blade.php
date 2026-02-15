@extends('layouts.app')

@section('title', $book->title . ' - PageTurner')

@section('content')
    <section id="billboard" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="banner-content">
                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 mb-3">
                            {{ $book->category->name }}
                        </span>

                        <h2 class="display-5 fw-bold mb-3">{{ $book->title }}</h2>
                        <h5 class="text-muted mb-3">by {{ $book->author }}</h5>

                        <p class="lead mb-4">{{ $book->description }}</p>

                        <div class="mb-4">
                            <span class="h2 fw-bold text-danger me-3">${{ number_format($book->price, 2) }}</span>
                            @if($book->stock_quantity > 0)
                                <span class="badge bg-success px-3 py-2">In Stock ({{ $book->stock_quantity }} available)</span>
                            @else
                                <span class="badge bg-secondary px-3 py-2">Out of Stock</span>
                            @endif
                        </div>

                        <p class="text-muted mb-3"><strong>ISBN:</strong> {{ $book->isbn }}</p>

                        <!-- Star Rating -->
                        <div class="d-flex align-items-center mb-4">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($book->average_rating))
                                    <svg width="24" height="24" viewBox="0 0 20 20" fill="#fbbf24" class="me-1">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @else
                                    <svg width="24" height="24" viewBox="0 0 20 20" fill="#d1d5db" class="me-1">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endif
                            @endfor
                            <span class="ms-2 text-muted">
                                {{ number_format($book->average_rating, 1) }} ({{ $book->reviews->count() }} reviews)
                            </span>
                        </div>

                        {{-- Order Form --}}
                        @auth
                            @if (!auth()->user()->isAdmin() && $book->stock_quantity > 0)
                                <form action="{{ route('orders.store') }}" method="POST" class="d-flex gap-3">
                                    @csrf
                                    <div class="d-flex align-items-center border rounded-3">
                                        <input type="number" name="quantity" min="1" max="{{ $book->stock_quantity }}" value="1"
                                            class="form-control border-0" style="width: 80px;">
                                    </div>
                                    <input type="hidden" name="price" value="{{ $book->price }}">
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <button type="submit" class="btn btn-primary px-4">
                                        Order Now
                                    </button>
                                </form>
                            @elseif(!auth()->user()->isAdmin() && $book->stock_quantity <= 0)
                                <button class="btn btn-secondary px-4" disabled>Out of Stock</button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary px-4">Login to Order</a>
                        @endauth
                    </div>
                </div>

                <div class="col-md-6 text-center">
                    <img src="{{ empty($book->cover_image) ? asset('booksaw/images/main-banner1.jpg') : asset($book->cover_image) }}"
                        alt="{{ $book->title }}" class="img-fluid rounded-3 shadow-lg"
                        style="max-height: 500px; min-height: 500px; min-width: 300px; max-width: 350px">
                </div>

                {{-- Admin Actions --}}
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="container mt-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex gap-3">
                                        <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning">
                                            Edit Book
                                        </a>
                                        <form action="{{ route('admin.books.destroy', $book) }}" method="POST"
                                            onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                Delete Book
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endauth
    </section>

    {{-- Reviews Section --}}
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="fw-bold mb-4">Customer Reviews</h2>

            {{-- Review Form --}}
            @auth
                @if (auth()->user()->hasPurchased($book->id))
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="mb-3">Write a Review</h5>
                            <form action="{{ route('reviews.store', $book) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <select name="rating" class="form-select" style="width: auto;" required>
                                        <option value="">Rating</option>
                                        @for($i = 5; $i >= 1; $i--)
                                            <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <textarea name="comment" rows="3" class="form-control"
                                        placeholder="Share your thoughts..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit Review</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="alert alert-info mb-4">
                        Purchase this book to write a review.
                    </div>
                @endif
            @else
                <div class="alert alert-info mb-4">
                    <a href="{{ route('login') }}">Login</a> to write a review.
                </div>
            @endauth

            {{-- Display Reviews --}}
            @forelse($book->reviews as $review)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3"
                                    style="width: 40px; height: 40px;">
                                    {{ substr($review->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $review->user->name }}</h6>
                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                            @auth
                                @if(auth()->id() === $review->user_id || auth()->user()->isAdmin())
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0"
                                            onclick="return confirm('Delete this review?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>

                        {{-- Star Rating --}}
                        <div class="d-flex align-items-center mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="#fbbf24" class="me-1">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @else
                                    <svg width="16" height="16" viewBox="0 0 20 20" fill="#d1d5db" class="me-1">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endif
                            @endfor
                        </div>

                        @isset($review->comment)
                            <p class="mb-0">{{ $review->comment }}</p>
                        @endisset
                    </div>
                </div>
            @empty
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No reviews yet. Be the first to review!</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .badge {
            font-weight: 500;
            border-radius: 30px;
        }
    </style>
@endsection