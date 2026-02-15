@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0 fw-bold">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2" style="color: #dc2626;">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                            </svg>
                            My Orders
                        </h3>
                        <span class="badge bg-light text-dark px-3 py-2">Total: {{ $orders->total() }} orders</span>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 ps-4">Order ID</th>
                                    <th class="py-3">Book Title</th>
                                    <th class="py-3 text-center">Quantity</th>
                                    <th class="py-3">Total Amount</th>
                                    <th class="py-3">Status</th>
                                    <th class="py-3">Order Date</th>
                                    <th class="py-3 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $order)
                                    @php
                                        $statusColors = [
                                            'pending' => ['bg' => '#fff3cd', 'text' => '#856404', 'dot' => '#ffc107'],
                                            'processing' => ['bg' => '#d1ecf1', 'text' => '#0c5460', 'dot' => '#17a2b8'],
                                            'completed' => ['bg' => '#d4edda', 'text' => '#155724', 'dot' => '#28a745'],
                                            'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24', 'dot' => '#dc3545']
                                        ];
                                        $statusStyle = $statusColors[$order->status] ?? $statusColors['pending'];
                                    @endphp
                                    <tr class="border-bottom">
                                        <td class="ps-4 fw-bold">#{{ $order->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1.5">
                                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="fw-medium">{{ $order->orderItems[0]->book->title }}</span>
                                                    @if($order->orderItems->count() > 1)
                                                        <small class="text-muted d-block">+{{ $order->orderItems->count() - 1 }} more item(s)</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark px-3 py-2">
                                                {{ $order->orderItems[0]->quantity }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">${{ number_format($order->total_amount ?? 0, 2) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="status-dot me-2" style="width: 8px; height: 8px; background: {{ $statusStyle['dot'] }}; border-radius: 50%; display: inline-block;"></span>
                                                <span class="badge px-3 py-2" style="background: {{ $statusStyle['bg'] }}; color: {{ $statusStyle['text'] }};">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" class="me-1">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <polyline points="12 6 12 12 16 14"></polyline>
                                                </svg>
                                                <span>{{ $order->created_at->format('M d, Y') }}</span>
                                                <small class="text-muted ms-1">{{ $order->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('orders.show', $order->id) }}" 
                                               class="btn btn-sm px-4" 
                                               style="background: #dc2626; color: white; border-radius: 20px;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                                    <circle cx="12" cy="8" r="1" fill="currentColor"></circle>
                                                </svg>
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="1" class="mb-3">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                            <h5 class="text-muted mb-2">No orders found</h5>
                                            <p class="text-muted mb-0">Start shopping to see your orders here!</p>
                                            <a href="{{ route('books.index') }}" class="btn btn-primary mt-3" style="background: #dc2626; border-color: #dc2626;">
                                                Browse Books
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted small">
                            Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders
                        </div>
                        <div>
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .table {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table thead th {
        border-bottom: 2px solid #dc2626;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        color: #4b5563;
    }
    
    .table tbody tr {
        transition: all 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: #fef2f2;
        transform: scale(1.01);
        box-shadow: 0 4px 12px rgba(220, 38, 38, 0.1);
    }
    
    .badge {
        font-weight: 500;
        border-radius: 30px;
    }
    
    .btn-sm {
        transition: all 0.2s;
    }
    
    .btn-sm:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 38, 38, 0.2);
    }
    
    .card {
        border-radius: 16px;
        overflow: hidden;
    }
    
    .alert {
        border-radius: 12px;
        border-left: 4px solid;
        display: flex;
        align-items: center;
    }
    
    .alert-success {
        border-left-color: #28a745;
    }
    
    .alert-danger {
        border-left-color: #dc3545;
    }
    
    .pagination {
        gap: 5px;
    }
    
    .pagination .page-link {
        border-radius: 8px;
        color: #dc2626;
        border: 1px solid #e5e7eb;
        padding: 8px 12px;
        transition: all 0.2s;
    }
    
    .pagination .page-link:hover {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
    }
    
    .pagination .active .page-link {
        background: #dc2626;
        border-color: #dc2626;
        color: white;
    }
    
    .status-dot {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            opacity: 1;
        }
    }
</style>
@endsection