<!-- Leads Table -->
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
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                        @php
                            $orderItem = $order->orderItems[0] ?? null;
                            
                            // Determine status based on order status
                            $statusClass = 'secondary';
                            $statusText = 'Pending';
                            
                            if($order->status == 'completed') {
                                $statusClass = 'success';
                                $statusText = 'Completed';
                            } elseif($order->status == 'processing') {
                                $statusClass = 'primary';
                                $statusText = 'Processing';
                            } elseif($order->status == 'shipped') {
                                $statusClass = 'info';
                                $statusText = 'Shipped';
                            } elseif($order->status == 'cancelled') {
                                $statusClass = 'danger';
                                $statusText = 'Cancelled';
                            } elseif($order->status == 'refunded') {
                                $statusClass = 'warning';
                                $statusText = 'Refunded';
                            }
                        @endphp
                        <tr class="single-item">
                            <td>
                                <div class="item-checkbox ms-1">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input checkbox" id="checkBox_{{ $order->id }}">
                                        <label class="custom-control-label" for="checkBox_{{ $order->id }}"></label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('orders.show', $order->id) }}" class="hstack gap-3">
                                    <div class="avatar-image avatar-md">
                                        <img src="{{ empty($order->user->profile_photo) ? asset('duralex/images/profile_default.png') : asset('storage/' . $order->user->profile_photo) }}" alt="user-image" class="img-fluid rounded-circle">
                                    </div>
                                    <div>
                                        <span class="text-truncate-1-line fw-bold">{{ $order->user->name }}</span>
                                        @if($orderItem)
                                            <small class="text-muted d-block">Order #: {{ $order->order_number ?? $order->id }}</small>
                                        @endif
                                    </div>
                                </a>
                            </td>
                            <td>{{ $order->user->email }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span>{{ $order->created_at->format('M d, Y') }}</span>
                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusClass }} px-3 py-2">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td>
                                <div class="hstack gap-2 justify-content-end">
                                    <a href="{{ route('admin.orderShow', $order->id) }}" class="avatar-text avatar-md" data-bs-toggle="tooltip" title="View Order">
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