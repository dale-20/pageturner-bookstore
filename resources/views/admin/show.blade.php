@extends('layouts.admin-layout')

@section('title', $book->title . ' - Admin - PageTurner')
@section('page-title', 'Book')
@section('breadcrumb', 'Book Details')

@section('content')
    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- Book Details Card -->
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <!-- Admin Actions Header -->
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ $book->title }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">Viewing complete book details and management options</span>
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-warning">
                                    <i class="feather-edit-3 me-2"></i>Edit Book
                                </a>
                                <form action="{{ route('admin.books.destroy', $book) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.');"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="feather-trash-2 me-2"></i>Delete Book
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Book Overview Section -->
                        <div class="row mb-5">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="book-cover-container">
                                    @if($book->cover_image)
                                        <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                             alt="{{ $book->title }}"
                                             class="img-fluid rounded-3 shadow-lg"
                                             style="max-height: 400px; width: auto;"
                                             onerror="this.onerror=null; this.src='{{ asset('duralex/images/default-book.png') }}';">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center bg-light rounded-3 shadow-lg mx-auto" 
                                             style="width: 300px; height: 400px;">
                                            <div class="text-center">
                                                <i class="feather-book-open" style="font-size: 4rem; color: #ccc;"></i>
                                                <p class="text-muted mt-3">No Cover Image</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex flex-column h-100">
                                    <div class="mb-4">
                                        <span class="badge bg-primary px-3 py-2 mb-3">{{ $book->category->name ?? 'Uncategorized' }}</span>
                                        <h2 class="fw-bold mb-2">{{ $book->title }}</h2>
                                        <h5 class="text-muted mb-3">by {{ $book->author }}</h5>
                                    </div>

                                    <!-- Quick Stats -->
                                    <div class="row mb-4">
                                        <div class="col-sm-4 mb-3">
                                            <div class="bg-light rounded-3 p-3 text-center">
                                                <span class="d-block fw-bold h3 mb-1">${{ number_format($book->price, 2) }}</span>
                                                <span class="text-muted">Price</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3">
                                            <div class="bg-light rounded-3 p-3 text-center">
                                                <span class="d-block fw-bold h3 mb-1">{{ $book->stock_quantity }}</span>
                                                <span class="text-muted">Stock Quantity</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3">
                                            <div class="bg-light rounded-3 p-3 text-center">
                                                <span class="d-block fw-bold h3 mb-1">{{ number_format($book->average_rating, 1) }}</span>
                                                <span class="text-muted">Avg. Rating</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ISBN -->
                                    <div class="mb-3">
                                        <strong>ISBN:</strong> {{ $book->isbn }}
                                    </div>

                                    <!-- Status -->
                                    <div class="mb-4">
                                        <strong>Status:</strong>
                                        @if($book->stock_quantity > 0)
                                            <span class="badge bg-success ms-2 px-3 py-2">In Stock</span>
                                        @else
                                            <span class="badge bg-danger ms-2 px-3 py-2">Out of Stock</span>
                                        @endif
                                    </div>

                                    <!-- Description -->
                                    <div class="mt-auto">
                                        <strong>Description:</strong>
                                        <p class="text-muted mt-2">{{ $book->description ?? 'No description provided.' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Tabs -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <ul class="nav nav-tabs" id="bookTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" 
                                                data-bs-target="#details" type="button" role="tab">
                                            <i class="feather-info me-2"></i>Details
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" 
                                                data-bs-target="#reviews" type="button" role="tab">
                                            <i class="feather-star me-2"></i>Reviews ({{ $book->reviews->count() }})
                                        </button>
                                    </li>
                                
                                </ul>

                                <div class="tab-content p-4 border border-top-0 rounded-bottom-3" id="bookTabsContent">
                                    <!-- Details Tab -->
                                    <div class="tab-pane fade show active" id="details" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th width="150">Book ID:</th>
                                                        <td>{{ $book->id }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Created:</th>
                                                        <td>{{ $book->created_at->format('F d, Y \a\t h:i A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Last Updated:</th>
                                                        <td>{{ $book->updated_at->format('F d, Y \a\t h:i A') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Cover Image:</th>
                                                        <td>
                                                            @if($book->cover_image)
                                                                <code>{{ $book->cover_image }}</code>
                                                            @else
                                                                <span class="text-muted">No cover image</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                            <div class="col-md-6">
                                                <table class="table table-borderless">
                                                    <tr>
                                                        <th width="150">Total Sold:</th>
                                                        <td>{{ $total_sold }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Revenue:</th>
                                                        <td>${{ $revenue }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Low Stock Alert:</th>
                                                        <td>
                                                            @if($book->stock_quantity <= 5 && $book->stock_quantity > 0)
                                                                <span class="badge bg-warning">Low Stock ({{ $book->stock_quantity }})</span>
                                                            @elseif($book->stock_quantity == 0)
                                                                <span class="badge bg-danger">Out of Stock</span>
                                                            @else
                                                                <span class="badge bg-success">Healthy Stock</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reviews Tab -->
                                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                                        @forelse($book->reviews as $review)
                                            <div class="card border-0 shadow-sm mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-text avatar-sm bg-primary text-white rounded-circle me-3">
                                                                {{ substr($review->user->name, 0, 1) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0">{{ $review->user->name }}</h6>
                                                                <small class="text-muted">{{ $review->created_at->format('M d, Y \a\t h:i A') }}</small>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <!-- Rating Stars -->
                                                            @for($i = 1; $i <= 5; $i++)
                                                                @if($i <= $review->rating)
                                                                    <i class="feather-star text-warning"></i>
                                                                @else
                                                                    <i class="feather-star text-muted opacity-25"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    @if($review->comment)
                                                        <p class="mb-2">{{ $review->comment }}</p>
                                                    @endif
                                                    <div class="d-flex justify-content-end">
                                                        <form action="{{ route('reviews.destroy', $review) }}" method="POST"
                                                              onsubmit="return confirm('Delete this review?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                                                Delete Review
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5">
                                                <i class="feather-star" style="font-size: 3rem; color: #ccc;"></i>
                                                <p class="text-muted mt-3 mb-0">No reviews yet for this book.</p>
                                            </div>
                                        @endforelse
                                    </div>

                    
                                </div>
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <a href="{{ route('admin.books.index') }}" class="btn btn-light">
                                    <i class="feather-arrow-left me-2"></i>Back to Books
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .book-cover-container {
        transition: transform 0.3s ease;
    }
    .book-cover-container:hover {
        transform: scale(1.02);
    }
    .nav-tabs .nav-link {
        color: #495057;
        font-weight: 500;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        font-weight: 600;
    }
    .avatar-text {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .feather-star {
        width: 16px;
        height: 16px;
    }
</style>
@endpush