@extends('layouts.admin-layout')

@section('title', 'Admin All Books - PageTurner')
@section('page-title', 'Books')
@section('breadcrumb', 'Books')


@section('add-features')
    <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
        <i class="feather-plus me-2"></i>
        <span>Create Book</span>
    </a>
@endsection


@section('content')
    <!-- Books Table -->
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="leadList">
                        <thead>
                            <tr>
                                <th class="wd-30">
                                    <div class="btn-group mb-1">
                                        <div class="custom-control custom-checkbox ms-1">
                                            <input type="checkbox" class="custom-control-input" id="checkAllLead">
                                            <label class="custom-control-label" for="checkAllLead"></label>
                                        </div>
                                    </div>
                                </th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>ISBN</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($books as $book)
                                <tr class="single-item">
                                    <td>
                                        <div class="item-checkbox ms-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input checkbox"
                                                    id="checkBox_{{ $book->id }}">
                                                <label class="custom-control-label" for="checkBox_{{ $book->id }}"></label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.books.show', $book) }}" class="hstack gap-3">
                                            <div class="avatar-image avatar-md" style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden;">
                                                @if(!empty($book->cover_image) && file_exists(public_path('storage/' . $book->cover_image)))
                                                    <img src="{{ asset('storage/' . $book->cover_image) }}"
                                                        alt="{{ $book->title }} cover"
                                                        class="img-fluid"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light" 
                                                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                        <span class="text-white fw-bold">{{ strtoupper(substr($book->title, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.books.show', $book) }}" class="text-decoration-none">
                                            <span class="text-truncate-1-line fw-bold">{{ $book->title }}</span>
                                            @if($book->subtitle)
                                                <small class="text-muted d-block">{{ $book->subtitle }}</small>
                                            @endif
                                        </a>
                                    </td>
                                    <td>{{ $book->author }}</td>
                                    <td>{{ $book->isbn }}</td>
                                    <td>
                                        <span class="fw-bold text-success">₱{{ number_format($book->price, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($book->stock_quantity > 10)
                                            <span class="badge bg-success">{{ $book->stock_quantity }} in stock</span>
                                        @elseif($book->stock_quantity > 0)
                                            <span class="badge bg-warning text-dark">{{ $book->stock_quantity }} low stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $book->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $book->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('admin.books.show', $book->id) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View Book">
                                                <i class="feather feather-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.books.edit', $book->id) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="Edit Book">
                                                <i class="feather feather-edit"></i>
                                            </a>
                                            <div class="dropdown">
                                                <a href="javascript:void(0)" class="avatar-text avatar-md" data-bs-toggle="dropdown" data-bs-offset="0,21">
                                                    <i class="feather feather-more-horizontal"></i>
                                                </a>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.books.show', $book->id) }}">
                                                            <i class="feather feather-eye me-3"></i>
                                                            <span>View Details</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('admin.books.edit', $book->id) }}">
                                                            <i class="feather feather-edit-3 me-3"></i>
                                                            <span>Edit Book</span>
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)" onclick="duplicateBook({{ $book->id }})">
                                                            <i class="feather feather-copy me-3"></i>
                                                            <span>Duplicate</span>
                                                        </a>
                                                    </li>
                                                    <li class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('admin.books.destroy', $book) }}" method="POST"
                                                            onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.');"
                                                            class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger" style="width: 100%;">
                                                                <i class="feather feather-trash-2 me-3"></i>
                                                                <span>Delete Book</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
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
    <script src="{{ asset('duralex/vendors/js/select2.min.js') }}"></script>
    <script src="{{ asset('duralex/vendors/js/select2-active.min.js') }}"></script>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Function to duplicate book
        function duplicateBook(bookId) {
            if(confirm('Duplicate this book?')) {
                // Add AJAX call here to duplicate book
                fetch('/admin/books/' + bookId + '/duplicate', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
            }
        }

        // Check all functionality
        document.getElementById('checkAllLead').addEventListener('change', function(e) {
            const checkboxes = document.querySelectorAll('.checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = e.target.checked;
            });
        });
    </script>
@endsection