<div class="col-12">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between py-3">
            <h6 class="mb-0 fw-bold">
                <i class="feather feather-trending-up me-2 text-warning"></i>Bestseller Stats by Category
            </h6>
            <small class="text-muted">From materialized view · refreshes hourly</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Total Books</th>
                            <th class="text-end">Avg Price</th>
                            <th class="text-end">Total Inventory</th>
                            <th class="text-end">Bestsellers <small class="text-muted">(500+ stock)</small></th>
                            <th>Latest Addition</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bestsellerStats as $row)
                        <tr>
                            <td class="fw-medium">{{ $row->category_name }}</td>
                            <td class="text-end">{{ number_format($row->total_books) }}</td>
                            <td class="text-end">₱{{ number_format($row->avg_price, 2) }}</td>
                            <td class="text-end">{{ number_format($row->total_inventory) }}</td>
                            <td class="text-end">
                                <span class="badge bg-warning text-dark">
                                    {{ number_format($row->bestseller_count) }}
                                </span>
                            </td>
                            <td class="text-muted small">{{ $row->latest_addition ?? '—' }}</td>
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