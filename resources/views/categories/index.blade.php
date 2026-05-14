@extends('layouts.admin-layout')

@section('title', 'Admin Categories - PageTurner')
@section('page-title', 'Categories')
@section('breadcrumb', 'Categories')

@section('add-features')
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="feather-plus me-2"></i>
        <span>Create Category</span>
    </a>
@endsection

@section('content')
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="categoriesList">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Books</th>
                                <th>Date Added</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category)
                                <tr class="single-item">
                                    <td>
                                        <div class="hstack gap-3">
                                            <div class="avatar-text avatar-md"
                                                style="background: #3454d1; color: #fff; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <span>{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-truncate-1-line fw-semibold">{{ $category->name }}</span>
                                                <small class="text-muted d-block">ID: {{ $category->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $category->description ?: 'No description' }}</td>
                                    <td>
                                        <span class="badge bg-info px-3 py-2">
                                            {{ $category->books_count }} {{ Str::plural('book', $category->books_count) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $category->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $category->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('admin.categories.show', $category->id) }}"
                                                class="avatar-text avatar-md"
                                                data-bs-toggle="tooltip" title="View Category">
                                                <i class="feather feather-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="avatar-text avatar-md"
                                                data-bs-toggle="tooltip" title="Edit Category">
                                                <i class="feather feather-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.categories.destroy', $category) }}"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="avatar-text avatar-md bg-danger text-white border-0"
                                                    style="cursor: pointer;" data-bs-toggle="tooltip" title="Delete Category">
                                                    <i class="feather feather-trash-2"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('duralex/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('#categoriesList').DataTable({
                "pageLength": 25,
                "ordering": true,
                "info": true,
                "searching": true,
                "lengthChange": true
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection