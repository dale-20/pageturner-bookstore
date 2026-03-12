{{-- Recent Orders Table --}}
<div class="col-lg-12">
    <div class="card stretch stretch-full">

        {{-- Card Header --}}
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0">Recent Orders</h5>
            <a href="{{ route('admin.orders', 'pending') }}" class="btn btn-sm btn-light-brand">
                View All <i class="feather-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover" id="dashboardOrderList">
                    <thead>
                        <tr>
                            <th class="wd-30">
                                <div class="custom-control custom-checkbox ms-1">
                                    <input type="checkbox" class="custom-control-input" id="checkAllLead">
                                    <label class="custom-control-label" for="checkAllLead"></label>
                                </div>
                            </th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                        @php
                            $statusMap = [
                                'completed'  => ['success',   'Completed'],
                                'processing' => ['primary',   'Processing'],
                                'shipped'    => ['info',      'Shipped'],
                                'cancelled'  => ['danger',    'Cancelled'],
                                'refunded'   => ['warning',   'Refunded'],
                                'pending'    => ['secondary', 'Pending'],
                            ];
                            [$statusClass, $statusText] = $statusMap[$order->status] ?? ['secondary', 'Pending'];
                        @endphp
                        <tr class="single-item">
                            <td>
                                <div class="custom-control custom-checkbox ms-1">
                                    <input type="checkbox" class="custom-control-input checkbox" id="checkBox_{{ $order->id }}">
                                    <label class="custom-control-label" for="checkBox_{{ $order->id }}"></label>
                                </div>
                            </td>

                            {{-- Customer --}}
                            <td>
                                <a href="{{ route('admin.orderShow', $order->id) }}" class="hstack gap-3">
                                    <div class="avatar-image avatar-md">
                                        <img src="{{ empty($order->user->profile_photo) ? asset('duralex/images/profile_default.png') : asset('storage/' . $order->user->profile_photo) }}"
                                             alt="user" class="img-fluid rounded-circle">
                                    </div>
                                    <div>
                                        <span class="text-truncate-1-line fw-bold">{{ $order->user->name }}</span>
                                        <small class="text-muted d-block">#{{ $order->id }}</small>
                                    </div>
                                </a>
                            </td>

                            {{-- Email --}}
                            <td>{{ $order->user->email }}</td>

                            {{-- Item count --}}
                            <td>
                                <span class="badge border" style="background-color: var(--bs-tertiary-bg); color: var(--bs-body-color);">
                                    {{ $order->orderItems->count() }} item{{ $order->orderItems->count() !== 1 ? 's' : '' }}
                                </span>
                            </td>

                            {{-- Total amount --}}
                            <td class="fw-semibold">
                                ₱{{ number_format($order->total_amount, 2) }}
                            </td>

                            {{-- Date --}}
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $order->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                    {{ $statusText }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ route('admin.orderShow', $order->id) }}"
                                       class="avatar-text avatar-md"
                                       data-bs-toggle="tooltip"
                                       title="View Order">
                                        <i class="feather feather-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="feather-inbox fs-2 d-block mb-2"></i>
                                No orders yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>