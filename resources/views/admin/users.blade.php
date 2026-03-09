@extends('layouts.admin-layout')

@section('title', 'All Customers - PageTurner')
@section('page-title', 'Users')
@section('breadcrumb', 'Customers')

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
                                <th>Name</th>
                                <th>Email</th>
                                <th>Total Spent</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="single-item">
                                    <td>
                                        <div class="item-checkbox ms-1">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input checkbox"
                                                    id="checkBox_{{ $user->id }}">
                                                <label class="custom-control-label" for="checkBox_{{ $user->id }}"></label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="hstack gap-3">
                                            <div class="avatar-image avatar-md" style="width: 50px; height: 50px; border-radius: 8px; overflow: hidden;">
                                                @if(!empty($user->profile_photo) && file_exists(public_path('storage/' . $user->profile_photo)))
                                                    <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                                        alt="{{ $user->name }} cover"
                                                        class="img-fluid"
                                                        style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-light" 
                                                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                        <span class="text-white fw-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.users.show', $user) }}" class="text-decoration-none">
                                            <span class="text-truncate-1-line fw-bold">{{ $user->name }}</span>
                                            {{-- @if($book->subtitle)
                                                <small class="text-muted d-block">{{ $book->subtitle }}</small>
                                            @endif --}}
                                        </a>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @php
                                            $total = 0;
                                            foreach($user->orders as $order){
                                                if($order->status == 'completed'){
                                                    $total += $order->total_amount;
                                                }
                                            }
                                        @endphp
                                        <span class="fw-bold text-success">₱{{ number_format($total, 2) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $user->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $user->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View Book">
                                                <i class="feather feather-eye"></i>
                                            </a>
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