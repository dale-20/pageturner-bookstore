<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <h6 class="mb-0 fw-bold">
                <i class="feather feather-package me-2 text-primary"></i>Inventory Summary
            </h6>
            <small class="text-muted">From materialized view · refreshes hourly</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Books</th>
                            <th class="text-end">Total Stock</th>
                            <th class="text-end">Total Value</th>
                            <th class="text-end">Out of Stock</th>
                            <th class="text-end">Low Stock <small class="text-muted">(&lt;10)</small></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventorySummary as $row)
                        <tr>
                            <td class="fw-medium">{{ $row->category_name }}</td>
                            <td class="text-end">{{ number_format($row->book_count) }}</td>
                            <td class="text-end">{{ number_format($row->total_stock) }}</td>
                            <td class="text-end">₱{{ number_format($row->total_value, 2) }}</td>
                            <td class="text-end">
                                @if($row->out_of_stock_count > 0)
                                    <span class="badge bg-danger">{{ number_format($row->out_of_stock_count) }}</span>
                                @else
                                    <span class="text-success small">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($row->low_stock_count > 0)
                                    <span class="badge bg-warning text-dark">{{ number_format($row->low_stock_count) }}</span>
                                @else
                                    <span class="text-success small">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                No data yet —
                                <code>php artisan app:refresh-materialized-views</code>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>