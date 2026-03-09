@extends('layouts.admin-layout')

@section('title', 'Admin Orders - PageTurner')
@section('page-title', 'Orders')
@section('breadcrumb', 'Orders')


@section('content')
    <!-- Orders Table -->
    <div class="col-lg-12">
        <div class="card stretch stretch-full">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="orderList">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Book Title</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date Ordered</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="single-item">
                                    <td>
                                        <span class="fw-semibold">#{{ $order->id }}</span>
                                    </td>
                                    <td>
                                        <div class="hstack gap-3">
                                            <div class="avatar-image avatar-md">
                                                <img src="{{ empty($order->user->profile_photo) ? asset('duralex/images/profile_default.png') : asset('storage/' . $order->user->profile_photo) }}"
                                                    alt="user-image" class="img-fluid">
                                            </div>
                                            <div>
                                                <span class="text-truncate-1-line">{{ $order->user->name ?? 'N/A' }}</span>
                                                <small class="text-muted d-block">{{ $order->user->email ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php $items = $order->orderItems; @endphp
                                        <span class="fw-semibold">{{ $items->first()->book->title ?? 'N/A' }}</span>
                                        <small class="text-muted d-block">x{{ $items->first()->quantity ?? 1 }}</small>
                                        @if($items->count() > 1)
                                            <a class="small text-primary" data-bs-toggle="collapse"
                                               href="#orderItems_{{ $order->id }}" role="button">
                                                +{{ $items->count() - 1 }} more
                                            </a>
                                            <div class="collapse mt-1" id="orderItems_{{ $order->id }}">
                                                @foreach($items->skip(1) as $item)
                                                    <div class="small text-muted">
                                                        {{ $item->book->title ?? 'N/A' }}
                                                        <span class="text-dark">x{{ $item->quantity }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'pending' => 'bg-warning',
                                                'processing' => 'bg-info',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                            ][$order->status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $statusClass }} px-3 py-2">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $order->created_at->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>

                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <!-- View Button -->
                                            <a href="{{ route('admin.orderShow', $order->id) }}" class="avatar-text avatar-md"
                                                data-bs-toggle="tooltip" title="View Order">
                                                <i class="feather feather-eye"></i>
                                            </a>

                                            <!-- Accept/Process Button (only for pending orders) -->
                                            @if($order->status === 'pending')
                                                <form action="{{ route('admin.order.status', $order) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Process this order?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" value="processing" name="status">
                                                    <button type="submit"
                                                        class="avatar-text avatar-md bg-success text-white border-0"
                                                        style="cursor: pointer;" data-bs-toggle="tooltip" title="Accept Order">
                                                        <i class="feather feather-check"></i>
                                                    </button>
                                                </form>

                                                <!-- Deny/Cancel Button -->
                                                <form action="{{ route('order.cancel', $order) }}" method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to deny this order?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" value="cancelled" name="status">
                                                    <button type="submit"
                                                        class="avatar-text avatar-md bg-danger text-white border-0"
                                                        style="cursor: pointer;" data-bs-toggle="tooltip" title="Deny Order">
                                                        <i class="feather feather-x"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($order->status === 'processing')
                                                <!-- Complete Button (Blue with check-circle) -->
                                                <form action="{{ route('admin.order.status', $order) }}" method="POST"
                                                    class="d-inline" onsubmit="return confirm('Mark this order as completed?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" value="completed" name="status">
                                                    <button type="submit"
                                                        class="avatar-text avatar-md bg-primary text-white border-0"
                                                        style="cursor: pointer;" data-bs-toggle="tooltip" title="Complete Order">
                                                        <i class="feather feather-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- <!-- Edit Button (only for non-completed/cancelled) -->
                                            @if(!in_array($order->status, ['completed', 'cancelled', 'declined']))
                                            <a href="{{ route('admin.orders.edit', $order->id) }}" class="avatar-text avatar-md"
                                                data-bs-toggle="tooltip" title="Edit Order">
                                                <i class="feather feather-edit"></i>
                                            </a>
                                            @endif --}}

                                            <!-- Delete Button (Admin only) -->
                                            {{-- @if(auth()->user()->isAdmin())
                                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this order? This action cannot be undone.');"
                                                class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="avatar-text avatar-md border-0 bg-transparent"
                                                    data-bs-toggle="tooltip" title="Delete Order">
                                                    <i class="feather feather-trash"></i>
                                                </button>
                                            </form>
                                            @endif --}}
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
            // Initialize DataTable
            $('#orderList').DataTable({
                "pageLength": 25,
                "ordering": true,
                "info": true,
                "searching": true,
                "lengthChange": true
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
@endsection