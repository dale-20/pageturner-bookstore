@extends('layouts.admin-layout')

@section('title', 'Edit Category - PageTurner')
@section('page-title', 'Categories')
@section('breadcrumb', 'Edit Category')

@section('content')
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <div class="card-body general-info">
                        <div class="mb-5 d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold mb-0 me-4">
                                <span class="d-block mb-2">Edit Category :</span>
                                <span class="fs-12 fw-normal text-muted text-truncate-1-line">Make changes to this category</span>
                            </h5>
                        </div>

                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Category Name Field -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="nameInput" class="fw-semibold">Category Name: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-tag"></i></div>
                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror" id="nameInput"
                                            placeholder="e.g., Fiction, Mystery, Science Fiction" 
                                            value="{{ old('name', $category->name) }}">
                                    </div>
                                    @error('name')
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
                                        <div class="input-group-text"><i class="feather-align-left"></i></div>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                            name="description" id="descriptionInput" rows="5"
                                            placeholder="Describe what kind of books belong in this category...">{{ old('description', $category->description) }}</textarea>
                                    </div>
                                    @error('description')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Optional: Provide a brief description of the category</small>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-6 mb-2 mb-md-0">
                                            <button type="submit" class="btn btn-primary w-100">
                                                <i class="feather-save me-2"></i>Update Category
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ route('admin.categories.index') }}" class="btn btn-light w-100">
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