@extends('layouts.admin-layout')

@section('title', 'Edit Books - PageTurner')
@section('page-title', 'Books')
@section('breadcrumb', 'Edit')

@section('content')
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">Edit Book :</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">Make some adjustments to this book or change it all.</span>
                            </h5>
                        </div>

                        <form action="{{ route('admin.books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- BOOK COVER DISPLAY SECTION - PROMINENT AT TOP -->
                            <div class="row mb-5">
                                <div class="col-12">
                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-lg-3 text-center">
                                                    <label class="fw-semibold d-block mb-2">Current Cover:</label>
                                                    <div class="book-cover-container mb-3 mb-lg-0">
                                                        @if($book->cover_image)
                                                            <img src="{{ asset('storage/' . $book->cover_image) }}" 
                                                                 alt="{{ $book->title }}"
                                                                 class="img-fluid rounded shadow"
                                                                 style="max-height: 180px; width: auto;"
                                                                 onerror="this.onerror=null; this.src='{{ asset('duralex/images/default-book.png') }}';">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center bg-white rounded shadow" 
                                                                 style="width: 130px; height: 180px; margin: 0 auto;">
                                                                <div class="text-center">
                                                                    <i class="feather-book-open" style="font-size: 3rem; color: #ccc;"></i>
                                                                    <p class="text-muted small mt-2">No Cover</p>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-lg-9">
                                                    <div class="d-flex flex-column">
                                                        <h4 class="fw-bold mb-2">{{ $book->title }}</h4>
                                                        <p class="text-muted mb-2">by {{ $book->author }}</p>
                                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                                            <span class="badge bg-primary p-2">ISBN: {{ $book->isbn }}</span>
                                                            <span class="badge bg-success p-2">${{ number_format($book->price, 2) }}</span>
                                                            <span class="badge bg-info p-2">Stock: {{ $book->stock_quantity }}</span>
                                                            <span class="badge bg-secondary p-2">{{ $book->category->name ?? 'Uncategorized' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Title Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="titleInput" class="fw-semibold">Title: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-book"></i></div>
                                        <input type="text" name="title"
                                            class="form-control @error('title') is-invalid @enderror" id="titleInput"
                                            placeholder="Title" value="{{ old('title', $book->title) }}">
                                    </div>
                                    @error('title')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Author Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="authorInput" class="fw-semibold">Author: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-user"></i></div>
                                        <input type="text" name="author"
                                            class="form-control @error('author') is-invalid @enderror" id="authorInput"
                                            placeholder="Author" value="{{ old('author', $book->author) }}">
                                    </div>
                                    @error('author')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Category Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="category_id" class="fw-semibold">Category: </label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control @error('category_id') is-invalid @enderror"
                                        name="category_id" id="category_id" data-select2-selector="category">
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" 
                                                {{ old('category_id', $book->category_id) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- ISBN Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="isbnInput" class="fw-semibold">ISBN: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-hash"></i></div>
                                        <input type="text" name="isbn"
                                            class="form-control @error('isbn') is-invalid @enderror" id="isbnInput"
                                            placeholder="ISBN" value="{{ old('isbn', $book->isbn) }}">
                                    </div>
                                    @error('isbn')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Price Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="priceInput" class="fw-semibold">Price: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-dollar-sign"></i></div>
                                        <input type="text" name="price"
                                            class="form-control @error('price') is-invalid @enderror" id="priceInput"
                                            inputmode="decimal" pattern="^\d+(\.\d{1,2})?$" placeholder="0.00"
                                            value="{{ old('price', $book->price) }}">
                                    </div>
                                    @error('price')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Stock Quantity Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="stock_quantityInput" class="fw-semibold">Stock Quantity: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-server"></i></div>
                                        <input type="number" name="stock_quantity"
                                            class="form-control @error('stock_quantity') is-invalid @enderror"
                                            id="stock_quantityInput" min="0" placeholder="0"
                                            value="{{ old('stock_quantity', $book->stock_quantity) }}">
                                    </div>
                                    @error('stock_quantity')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="descriptionInput" class="fw-semibold">Description: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-type"></i></div>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            name="description" id="descriptionInput" cols="30" rows="5"
                                            placeholder="Book description">{{ old('description', $book->description) }}</textarea>
                                    </div>
                                    @error('description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- BOOK COVER IMAGE FIELD -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="cover_image" class="fw-semibold">Update Cover Image: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-image"></i></div>
                                        <input type="file" name="cover_image"
                                            class="form-control @error('cover_image') is-invalid @enderror" id="cover_image"
                                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                                    </div>
                                    @error('cover_image')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty to keep current cover. Accepted formats: JPEG, PNG, JPG, GIF, WEBP (Max: 2MB)</small>

                                    <!-- Image Preview for New Upload -->
                                    <div class="mt-3" id="imagePreview" style="display: none;">
                                        <p class="small text-muted mb-2">New Cover Preview:</p>
                                        <img src="#" alt="New Cover Preview" class="img-fluid rounded border" style="max-height: 120px;">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="feather-save me-2"></i>Update Book
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.books.index') }}" class="btn btn-light w-100">
                                                <i class="feather-x me-2"></i>Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
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
        transform: scale(1.05);
    }
</style>
@endpush

@push('scripts')
    <script>
        // Image preview functionality
        document.getElementById('cover_image').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = preview.querySelector('img');

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                previewImg.src = '#';
            }
        });
    </script>
@endpush