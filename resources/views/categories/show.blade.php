@extends('layouts.admin-layout')

@section('title', $category->name . ' - Category - PageTurner')
@section('page-title', 'Category Details')
@section('breadcrumb', 'Category Details')

@section('content')
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <!-- Category Details Card -->
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <!-- Admin Actions Header -->
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">{{ $category->name }}</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">Viewing category details and associated books</span>
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning">
                                    <i class="feather-edit-3 me-2"></i>Edit Category
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" {{ $category->books_count > 0 ? 'disabled' : '' }}>
                                        <i class="feather-trash-2 me-2"></i>Delete Category
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Category Overview Section -->
                        <div class="row mb-5">
                            <div class="col-md-8">
                                <div class="d-flex flex-column h-100">
                                    <div class="mb-4">
                                        <span class="badge bg-primary px-3 py-2 mb-3">Category ID: #{{ $category->id }}</span>
                                        <h2 class="fw-bold mb-3">{{ $category->name }}</h2>
                                        
                                        @if($category->description)
                                            <div class="mb-4">
                                                <h6 class="fw-semibold mb-2">Description:</h6>
                                                <p class="text-muted">{{ $category->description }}</p>
                                            </div>
                                        @else
                                            <p class="text-muted fst-italic">No description provided for this category.</p>
                                        @endif
                                    </div>

                                    <!-- Quick Stats -->
                                    <div class="row mt-auto">
                                        <div class="col-sm-4 mb-3">
                                            <div class="bg-light rounded-3 p-3 text-center">
                                                <span class="d-block fw-bold h3 mb-1">{{ $category->books_count ?? $category->books()->count() }}</span>
                                                <span class="text-muted">Total Books</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3">
                                            <div class="bg-light rounded-3 p-3 text-center">
                                                <span class="d-block fw-bold h3 mb-1">{{ $category->created_at->format('M d, Y') }}</span>
                                                <span class="text-muted">Created</span>
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3">
                                            <div class="bg-light rounded-3 p-3 text-center">
                                                <span class="d-block fw-bold h3 mb-1">{{ $category->updated_at->format('M d, Y') }}</span>
                                                <span class="text-muted">Last Updated</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Books in this Category Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0">Books in this Category</h5>
                                    <a href="{{ route('admin.books.create', ['category_id' => $category->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="feather-plus me-2"></i>Add New Book
                                    </a>
                                </div>

                                @if($books->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Cover</th>
                                                    <th>Title</th>
                                                    <th>Author</th>
                                                    <th>ISBN</th>
                                                    <th>Price</th>
                                                    <th>Stock</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($books as $book)
                                                <tr>
                                                    <td>{{ $book->id }}</td>
                                                    <td>
                                                        @if($book->cover_image)
                                                            <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                                                 alt="{{ $book->title }}"
                                                                 style="width: 40px; height: 50px; object-fit: cover;"
                                                                 class="rounded">
                                                        @else
                                                            <div class="bg-secondary bg-opacity-10 rounded" 
                                                                 style="width: 40px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                                <i class="feather-book" style="color: #ccc;"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.books.show', $book) }}" class="text-dark fw-semibold">
                                                            {{ Str::limit($book->title, 30) }}
                                                        </a>
                                                    </td>
                                                    <td>{{ Str::limit($book->author, 20) }}</td>
                                                    <td>{{ $book->isbn }}</td>
                                                    <td>${{ number_format($book->price, 2) }}</td>
                                                    <td>
                                                        @if($book->stock_quantity > 0)
                                                            <span class="badge bg-success">{{ $book->stock_quantity }}</span>
                                                        @else
                                                            <span class="badge bg-danger">0</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="hstack gap-2 justify-content-end">
                                                            <a href="{{ route('admin.books.show', $book) }}" 
                                                               class="avatar-text avatar-md">
                                                                <i class="feather feather-eye"></i>
                                                            </a>
                                                            <a href="{{ route('admin.books.edit', $book) }}" 
                                                               class="avatar-text avatar-md">
                                                                <i class="feather feather-edit-3"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Pagination -->
                                    <div class="mt-4">
                                        {{ $books->links() }}
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="feather-book-open" style="font-size: 3rem; color: #ccc;"></i>
                                        <p class="text-muted mt-3 mb-3">No books found in this category.</p>
                                        <a href="{{ route('admin.books.create', ['category_id' => $category->id]) }}" 
                                           class="btn btn-primary">
                                            <i class="feather-plus me-2"></i>Add Your First Book
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Back Button -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
                                    <i class="feather-arrow-left me-2"></i>Back to Categories
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
    .avatar-text {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    .avatar-text:hover {
        background-color: #f8f9fa;
    }
    .badge {
        font-weight: 500;
    }
</style>
@endpush