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
    <!-- Categories Table -->
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Categories</h5>
                <div class="d-flex gap-2">
                    <span class="badge bg-primary">Total: {{ $categories->count() }} categories</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="categoriesTable">
                        <thead>
                            <tr>
                                <th class="wd-30">
                                    <div class="btn-group mb-1">
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" class="custom-control-input" id="checkAllCategories">
                                            <label class="custom-control-label" for="checkAllCategories"></label>
                                        </div>
                                    </div>
                                </th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Books Count</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr class="single-item">
                                    <td>
                                        <div class="item-checkbox ms-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input checkbox"
                                                    id="checkBox_{{ $category->id }}">
                                                <label class="custom-control-label" for="checkBox_{{ $category->id }}"></label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-text avatar-sm" 
                                                 style="background: {{ $category->color ?? '#667eea' }}; 
                                                        width: 35px; height: 35px; 
                                                        display: flex; align-items: center; justify-content: center;">
                                                @if($category->icon)
                                                    <i class="feather-{{ $category->icon }} text-white"></i>
                                                @else
                                                    <span class="text-white fw-bold">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <strong class="d-block">{{ $category->name }}</strong>
                                                @if($category->slug)
                                                    <small class="text-muted">Slug: {{ $category->slug }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" title="{{ $category->description }}">
                                            {{ $category->description ?: 'No description' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info rounded-pill">
                                            {{ $category->books_count ?? $category->books->count() ?? 0 }} books
                                        </span>
                                    </td>
                                    <td>
                                        @if($category->status === 'active' || !isset($category->status))
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
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
                                                class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View Category">
                                                <i class="feather feather-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.categories.edit', $category->id) }}"
                                                class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Edit Category">
                                                <i class="feather feather-edit"></i>
                                            </a>
                                            <button type="button" 
                                                class="avatar-text avatar-md border-0 bg-transparent" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $category->id }}"
                                                title="Delete Category">
                                                <i class="feather feather-trash text-danger"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="feather feather-folder" style="font-size: 48px; color: #ccc;"></i>
                                            <h5 class="mt-3">No Categories Found</h5>
                                            <p class="text-muted">Get started by creating your first category</p>
                                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary mt-2">
                                                <i class="feather-plus me-2"></i>Create Category
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($categories->hasPages())
            <div class="card-footer">
                {{ $categories->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Delete Modals -->
    @foreach($categories as $category)
    <div class="modal fade" id="deleteModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>"{{ $category->name }}"</strong>?</p>
                    <p class="text-danger mb-0">
                        <small>
                            <i class="feather-alert-triangle me-1"></i>
                            This action cannot be undone. Any books in this category may become uncategorized.
                        </small>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="feather-trash-2 me-2"></i>Delete Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endsection

@section('scripts')
    <script src="{{ asset('duralex/vendors/js/dataTables.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/dataTables.bs5.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/select2.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/select2-active.min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable with custom options
            $('#categoriesTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    emptyTable: "No categories available",
                    info: "Showing _START_ to _END_ of _TOTAL_ categories",
                    infoEmpty: "Showing 0 to 0 of 0 categories",
                    search: "Search categories:",
                },
                columnDefs: [
                    { orderable: false, targets: [0, 6] } // Disable sorting on checkbox and actions columns
                ]
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Check all functionality
            $('#checkAllCategories').on('change', function() {
                $('.checkbox').prop('checked', $(this).prop('checked'));
            });

            // Individual checkbox changes affect "check all"
            $('.checkbox').on('change', function() {
                if ($('.checkbox:checked').length === $('.checkbox').length) {
                    $('#checkAllCategories').prop('checked', true);
                } else {
                    $('#checkAllCategories').prop('checked', false);
                }
            });

            // Add hover effect to rows
            $('.single-item').hover(
                function() { $(this).addClass('bg-light'); },
                function() { $(this).removeClass('bg-light'); }
            );
        });

        // Optional: Add keyboard shortcut for search (Ctrl+K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                $('.dataTables_filter input').focus();
            }
        });
    </script>

    <style>
        /* Custom styles for categories table */
        #categoriesTable thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        #categoriesTable tbody tr:hover {
            cursor: pointer;
        }
        
        .dataTables_filter input {
            border: 1px solid #dee2e6;
            border-radius: 20px;
            padding: 0.375rem 1rem;
            margin-left: 0.5rem;
        }
        
        .dataTables_filter input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        
        .dataTables_length select {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 0.375rem 1.75rem 0.375rem 0.75rem;
        }
        
        .avatar-text {
            transition: all 0.2s ease;
        }
        
        .avatar-text:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5em 0.8em;
        }
        
        .table td {
            vertical-align: middle;
        }
    </style>
@endsection