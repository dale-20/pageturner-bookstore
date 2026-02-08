@extends('layouts.app')

@section('title', 'All Books - PageTurner')

@section('content')
    <section class="py-5">
        <div class="container">
            {{-- Page Header --}}
            <div class="row mb-5">
                <div class="col-md-12">
                    <h1 class="display-5 fw-bold mb-4 text-primary">Browse Our Collection</h1>
                    <p class="lead text-muted">Discover your next favorite book from our curated selection</p>
                </div>
            </div>

            {{-- Search and Filter Section --}}
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="card shadow border-0">
                        <div class="card-body p-4">
                            <form action="{{ route('books.index') }}" method="GET" class="row g-3">
                                <div class="col-md-5">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="icon icon-search"></i>
                                        </span>
                                        <input type="text" 
                                               name="search" 
                                               value="{{ request('search') }}"
                                               placeholder="Search books by title, author, or description..."
                                               class="form-control border-start-0 ps-0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <select name="category" class="form-select form-select-lg">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                            <i class="icon icon-search me-2"></i> Search
                                        </button>
                                    </div>
                                </div>

                                @if(request()->has('search') || request()->has('category'))
                                    <div class="col-md-12 mt-3">
                                        <div class="alert alert-light d-flex align-items-center justify-content-between">
                                            <div>
                                                <small class="text-muted">Active filters:</small>
                                                @if(request()->has('search'))
                                                    <span class="badge bg-info ms-2">
                                                        Search: "{{ request('search') }}"
                                                    </span>
                                                @endif
                                                @if(request()->has('category'))
                                                    @php
                                                        $selectedCategory = $categories->firstWhere('id', request('category'));
                                                    @endphp
                                                    @if($selectedCategory)
                                                        <span class="badge bg-info ms-2">
                                                            Category: {{ $selectedCategory->name }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            <a href="{{ route('books.index') }}" class="text-decoration-none small">
                                                Clear all
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Results Count --}}
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <span class="text-muted">Found</span>
                            <strong class="text-primary">{{ $books->total() }}</strong>
                            <span class="text-muted">book{{ $books->total() !== 1 ? 's' : '' }}</span>
                        </h5>
                    </div>
                </div>
            </div>

            {{-- Books Grid --}}
            @if($books->count() > 0)
                <div class="row g-4">
                    @foreach($books as $book)
                            <x-book-card :book="$book" />
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($books->hasPages())
                    <div class="row mt-5">
                        <div class="col-md-12">
                            <nav aria-label="Books pagination">
                                {{ $books->withQueryString()->links('pagination::bootstrap-5') }}
                            </nav>
                        </div>
                    </div>
                @endif
            @else
                {{-- No Results --}}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body text-center py-5">
                                <div class="mb-4">
                                    <svg style="width: 80px; height: 80px; color: #adb5bd;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <h4 class="text-muted mb-3">No Books Found</h4>
                                <p class="text-muted mb-4">
                                    @if(request()->has('search') || request()->has('category'))
                                        No books match your search criteria. Try adjusting your filters.
                                    @else
                                        No books are currently available in our collection.
                                    @endif
                                </p>
                                @if(request()->has('search') || request()->has('category'))
                                    <a href="{{ route('books.index') }}" class="btn btn-primary">
                                        <i class="icon icon-x me-2"></i> Clear Filters
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection