@extends('layouts.app')

@section('title', $book->title . ' - PageTurner')

@section('content')

    <section id="billboard">
        <div class="container">
            <div class="row">
                <div class="col-md-12">

                    <div class="main-slider pattern-overlay">
                        <div class="slider-item">
                            <div class="banner-content">
                                <h3>{{ $book->category->name }}</h3>
                                <h2 class="banner-title">{{ $book->title }}</h2>
                                <h3>{{ $book->author }}</h3>
                                <p>{{ $book->description }}</p>
                                <h3>${{ $book->price }}</h3>
                                <p>
                                    @if($book->stock_quantity > 0)
                                        In Stock ({{ $book->stock_quantity }} available)
                                    @else
                                        Out of Stock
                                    @endif
                                </p>
                                <p class="text-gray-600 text-sm"><strong>ISBN:</strong> {{ $book->isbn }}</p>

                                <!-- Small stars with inline styles -->
                                <div style="display: flex; align-items: center; gap: 2px; margin-bottom: 8px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= round($book->average_rating))
                                            <svg style="width: 30px; height: 30px; color: #fbbf24;" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @else
                                            <svg style="width: 30px; height: 30px; color: #d1d5db;" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endif
                                    @endfor
                                    <span
                                        style="margin-left: 4px; font-size: 15px; color: #6b7280;">{{ number_format($book->average_rating, 1) }}
                                        ({{ $book->reviews->count() }} reviews)</span>
                                </div>
                            </div><!--banner-content-->
                            <div class="slider-item position-relative" style="height: 700px;">

                                <div class="position-absolute top-50 start-50 translate-middle" style="z-index: 1;">
                                    <img src="{{ empty($book->cover_image) ? asset('booksaw/images/main-banner1.jpg') : asset($book->cover_image) }}"
                                        alt="{{ $book->title }}" class="img-fluid rounded shadow-lg"
                                        style="max-height: 1500px; width: auto;">
                                </div>
                            </div>

                        </div><!--slider-item-->

                    </div><!--slider-->


                </div>
            </div>
        </div>

        {{-- Admin Actions --}}
        @auth
            @if(auth()->user()->isAdmin())
                <div class="mt-6 flex space-x-4">
                    <a href="{{ route('admin.books.edit', $book) }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 transition">
                        Edit Book
                    </a>
                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST"
                        onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                            Delete Book
                        </button>
                    </form>
                </div>
                @if (!auth()->user()->isAdmin())
                    <div class="mt-6 flex space-x-4">
                        <form action="{{ route('admin.books.destroy', $book) }}" method="POST"
                            onsubmit="return confirm('Are you sure?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
                                Order
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        @endauth
    </section>

    {{-- Reviews Section --}}
    <div class="mt-8">
        <h2 class="text-2xl font-bold mb-6">Customer Reviews</h2>

        {{-- Review Form (for authenticated users) --}}
        @auth
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="font-semibold text-lg mb-4">Write a Review</h3>
                <form action="{{ route('reviews.store', $book) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Rating</label>
                        <select name="rating"
                            class="border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            <option value="">Select rating</option>
                            @for($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Comment</label>
                        <textarea name="comment" rows="4"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Share your thoughts about this book..."></textarea>
                    </div>
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">
                        Submit Review
                    </button>
                </form>
            </div>
        @else
            <x-alert type="info" class="mb-6">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Login</a> to write a review.
            </x-alert>
        @endauth

        {{-- Display Reviews --}}
        @forelse($book->reviews as $review)
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center rounded-circle me-3"
                                    style="width: 40px; height: 40px;">
                                    {{ substr($review->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $review->user->name }}</h6>
                                    <small class="text-muted">{{ $review->created_at->format('M d, Y') }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-3">{{ $review->created_at->diffForHumans() }}</span>
                            @auth
                                @if(auth()->id() === $review->user_id || auth()->user()->isAdmin())
                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link text-danger p-0"
                                            onclick="return confirm('Are you sure you want to delete this review?')"
                                            title="Delete review">
                                            <i class="icon icon-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Star Rating -->
                    <div class="d-flex align-items-center mb-3">
                        <div style="display: flex; align-items: center; gap: 2px; margin-right: 10px;">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $review->rating)
                                    <svg style="width: 20px; height: 20px; color: #ffc107;" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @else
                                    <svg style="width: 20px; height: 20px; color: #e4e5e9;" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endif
                            @endfor
                        </div>
                        <span class="badge bg-light text-dark fs-6 px-3 py-1">
                            {{ number_format($review->rating, 1) }} / 5
                        </span>
                    </div>

                    <!-- Review Comment -->
                    @isset($review->comment)
                        <div class="review-content">
                            <p class="mb-0" style="line-height: 1.6;">
                                {{ $review->comment }}
                            </p>
                        </div>
                    @else
                        <div class="review-content">
                            <p class="text-muted fst-italic mb-0">
                                No comment provided.
                            </p>
                        </div>
                    @endisset
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div class="mb-3">
                    <svg style="width: 64px; height: 64px; color: #6c757d;" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <h4 class="text-muted mb-2">No Reviews Yet</h4>
                <p class="text-muted mb-4">Be the first to share your thoughts about this book!</p>
                @guest
                    <a href="{{ route('login') }}" class="btn btn-primary">
                        <i class="icon icon-user me-2"></i>Login to Review
                    </a>
                @endguest
            </div>
        @endforelse
    </div>

@endsection