@extends('layouts.admin-layout')

@section('title', 'Export Data - PageTurner')

@push('styles')
<style>
    /* ── PageTurner Export – dark-mode-aware editorial skin ── */
    :root {
        --pt-ink:        #1a1a2e;
        --pt-ink-soft:   #2d2d44;
        --pt-paper:      #f7f5f0;
        --pt-surface:    #ffffff;
        --pt-accent:     #c0392b;
        --pt-muted:      #7f8c8d;
        --pt-border:     #e2ddd6;
        --pt-success:    #27ae60;
        --pt-warn:       #e67e22;
        --pt-radius:     10px;
        --pt-shadow:     0 2px 16px rgba(26,26,46,.07);

        --pt-alert-info-bg:     #eef6fb;
        --pt-alert-info-border: #aed6f1;
        --pt-alert-info-color:  #1a5276;
        --pt-alert-ok-bg:       #eafaf1;
        --pt-alert-ok-border:   #a9dfbf;
        --pt-alert-ok-color:    #1e8449;
        --pt-alert-warn-bg:     #fef9e7;
        --pt-alert-warn-border: #f9e79f;
        --pt-alert-warn-color:  #9a7d0a;
        --pt-alert-err-bg:      #fdedec;
        --pt-alert-err-border:  #f5b7b1;
        --pt-alert-err-color:   #922b21;
    }

    .dark {
        --pt-ink:        #e8e6e1;
        --pt-ink-soft:   #c8c5c0;
        --pt-paper:      #1e1e2e;
        --pt-surface:    #252535;
        --pt-accent:     #e05c4b;
        --pt-muted:      #8a8a9a;
        --pt-border:     #3a3a50;
        --pt-success:    #2ecc71;
        --pt-warn:       #f39c12;
        --pt-shadow:     0 2px 16px rgba(0,0,0,.3);

        --pt-alert-info-bg:     #1a2a38;
        --pt-alert-info-border: #2a5278;
        --pt-alert-info-color:  #7dc3e8;
        --pt-alert-ok-bg:       #1a2e22;
        --pt-alert-ok-border:   #2e6b44;
        --pt-alert-ok-color:    #58d68d;
        --pt-alert-warn-bg:     #2e2a1a;
        --pt-alert-warn-border: #7d6a12;
        --pt-alert-warn-color:  #f4d03f;
        --pt-alert-err-bg:      #2e1a1a;
        --pt-alert-err-border:  #7d2a22;
        --pt-alert-err-color:   #f1948a;
    }

    /* ── Page wrapper ── */
    .pt-export-page {
        font-family: 'Georgia', serif;
        max-width: 820px;
        margin: 0 auto;
        padding: 0 1rem 3rem;
    }

    /* ── Page header ── */
    .pt-page-header {
        display: flex;
        align-items: flex-end;
        gap: 1rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--pt-ink);
    }
    .pt-page-header .pt-icon-wrap {
        width: 48px; height: 48px;
        background: var(--pt-ink);
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .pt-page-header .pt-icon-wrap i { color: var(--pt-surface); font-size: 1.25rem; }
    .pt-page-header h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--pt-ink);
        margin: 0;
        letter-spacing: -.02em;
    }
    .pt-page-header p {
        margin: 0;
        color: var(--pt-muted);
        font-size: .875rem;
        font-family: sans-serif;
    }

    /* ── Cards ── */
    .pt-card {
        background: var(--pt-surface);
        border: 1px solid var(--pt-border);
        border-radius: var(--pt-radius);
        box-shadow: var(--pt-shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .pt-card-header {
        padding: .85rem 1.25rem;
        border-bottom: 1px solid var(--pt-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--pt-paper);
    }
    .pt-card-header h2 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--pt-ink);
        margin: 0;
        font-family: sans-serif;
        letter-spacing: .01em;
    }
    .pt-card-body { padding: 1.5rem; }

    /* ── Form fields ── */
    .pt-label {
        display: block;
        font-family: sans-serif;
        font-size: .8rem;
        font-weight: 700;
        color: var(--pt-muted);
        text-transform: uppercase;
        letter-spacing: .06em;
        margin-bottom: .4rem;
    }
    .pt-field {
        width: 100%;
        padding: .5rem .75rem;
        border: 1px solid var(--pt-border);
        border-radius: 6px;
        background: var(--pt-paper);
        color: var(--pt-ink);
        font-family: sans-serif;
        font-size: .875rem;
        transition: border-color .15s, box-shadow .15s;
        appearance: none;
        -webkit-appearance: none;
    }
    .pt-field:focus {
        outline: none;
        border-color: var(--pt-ink);
        box-shadow: 0 0 0 3px rgba(26,26,46,.1);
    }
    .dark .pt-field:focus {
        box-shadow: 0 0 0 3px rgba(232,230,225,.12);
    }
    .pt-field-group { margin-bottom: 1.1rem; }

    /* Select arrow */
    .pt-select-wrap { position: relative; }
    .pt-select-wrap::after {
        content: '';
        position: absolute;
        right: .75rem;
        top: 50%;
        transform: translateY(-50%);
        border: 5px solid transparent;
        border-top-color: var(--pt-muted);
        pointer-events: none;
        margin-top: 3px;
    }
    .pt-select-wrap select { padding-right: 2rem; }

    /* Two-column price/date row */
    .pt-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 540px) { .pt-row-2 { grid-template-columns: 1fr; } }

    /* Section divider label */
    .pt-section-label {
        font-family: sans-serif;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--pt-muted);
        margin: 1.4rem 0 .85rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .pt-section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--pt-border);
    }

    /* ── Action buttons ── */
    .pt-actions { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; margin-top: 1.4rem; }
    .pt-btn {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .55rem 1.25rem;
        border-radius: 6px;
        font-family: sans-serif;
        font-size: .875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: opacity .15s, transform .1s;
        text-decoration: none;
    }
    .pt-btn:hover { opacity: .88; text-decoration: none; }
    .pt-btn:active { transform: scale(.98); }
    .pt-btn-primary { background: var(--pt-ink); color: var(--pt-surface); }
    .pt-btn-outline { background: transparent; border: 1.5px solid var(--pt-border); color: var(--pt-ink); }
    .pt-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    /* ── Alerts ── */
    .pt-alert {
        border-radius: var(--pt-radius);
        padding: .85rem 1rem;
        font-family: sans-serif;
        font-size: .875rem;
        display: flex;
        gap: .6rem;
        align-items: flex-start;
    }
    .pt-alert i { margin-top: .1rem; flex-shrink: 0; }
    .pt-alert-info    { background: var(--pt-alert-info-bg);  border: 1px solid var(--pt-alert-info-border);  color: var(--pt-alert-info-color); }
    .pt-alert-success { background: var(--pt-alert-ok-bg);    border: 1px solid var(--pt-alert-ok-border);    color: var(--pt-alert-ok-color); }
    .pt-alert-danger  { background: var(--pt-alert-err-bg);   border: 1px solid var(--pt-alert-err-border);   color: var(--pt-alert-err-color); }

    /* ── Result card ── */
    .pt-result {
        margin-top: 1.25rem;
        padding: 1.1rem 1.25rem;
        border-radius: var(--pt-radius);
        background: var(--pt-alert-ok-bg);
        border: 1px solid var(--pt-alert-ok-border);
        font-family: sans-serif;
    }
    .pt-result-title {
        font-weight: 700;
        font-size: .95rem;
        color: var(--pt-alert-ok-color);
        display: flex;
        align-items: center;
        gap: .45rem;
        margin-bottom: .6rem;
    }
    .pt-result-meta {
        font-size: .8rem;
        color: var(--pt-muted);
        margin-bottom: .85rem;
    }

    /* ── Spinner ── */
    @keyframes pt-spin { to { transform: rotate(360deg); } }
    .pt-spinner {
        width: 14px; height: 14px;
        border: 2px solid rgba(255,255,255,.35);
        border-top-color: #fff;
        border-radius: 50%;
        animation: pt-spin .7s linear infinite;
        display: inline-block;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
<div class="pt-export-page">

    {{-- Page Header --}}
    <div class="pt-page-header">
        <div class="pt-icon-wrap"><i class="feather-download-cloud"></i></div>
        <div>
            <h1>Export Books</h1>
            <p>Download your book catalogue as a filtered CSV file</p>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('admin.export.logs') }}" class="pt-btn pt-btn-outline">
                <i class="feather-list"></i> View Logs
            </a>
            <a href="{{ route('admin.import.form') }}" class="pt-btn pt-btn-outline">
                <i class="feather-upload"></i> Import
            </a>
        </div>
    </div>

    @if(isset($categories) && count($categories) > 0)

    {{-- Filters Card --}}
    <div class="pt-card">
        <div class="pt-card-header">
            <h2><i class="feather-filter me-2"></i>Export Filters</h2>
        </div>
        <div class="pt-card-body">

            {{-- Format (hidden; only CSV for now) --}}
            <input type="hidden" id="format" value="csv">

            <div class="pt-field-group">
                <label class="pt-label" for="category">Category</label>
                <div class="pt-select-wrap">
                    <select id="category" class="pt-field">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pt-section-label">Price range</div>
            <div class="pt-row-2">
                <div class="pt-field-group">
                    <label class="pt-label" for="min_price">Min Price</label>
                    <input type="number" id="min_price" class="pt-field" step="0.01" min="0" placeholder="0.00">
                </div>
                <div class="pt-field-group">
                    <label class="pt-label" for="max_price">Max Price</label>
                    <input type="number" id="max_price" class="pt-field" step="0.01" min="0" placeholder="9999.99">
                </div>
            </div>

            <div class="pt-field-group">
                <label class="pt-label" for="stock_status">Stock Status</label>
                <div class="pt-select-wrap">
                    <select id="stock_status" class="pt-field">
                        <option value="">All</option>
                        <option value="in_stock">In Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                        <option value="low_stock">Low Stock (≤ 10)</option>
                    </select>
                </div>
            </div>

            <div class="pt-section-label">Date added</div>
            <div class="pt-row-2">
                <div class="pt-field-group">
                    <label class="pt-label" for="date_from">From</label>
                    <input type="date" id="date_from" class="pt-field">
                </div>
                <div class="pt-field-group">
                    <label class="pt-label" for="date_to">To</label>
                    <input type="date" id="date_to" class="pt-field">
                </div>
            </div>

            <div class="pt-actions">
                <button type="button" id="exportBtn" class="pt-btn pt-btn-primary" onclick="performExport()">
                    <i class="feather-download"></i> Export Books
                </button>
                <button type="button" class="pt-btn pt-btn-outline" onclick="resetFilters()">
                    <i class="feather-x"></i> Reset
                </button>
            </div>

            {{-- Status / result / error --}}
            <div id="exportStatus"  style="display:none;" class="mt-3"></div>
            <div id="exportResult"  style="display:none;" class="mt-3"></div>
            <div id="exportError"   style="display:none;" class="mt-3"></div>

        </div>
    </div>

    @else

    <div class="pt-card">
        <div class="pt-card-body">
            <div class="pt-alert pt-alert-info">
                <i class="feather-info"></i>
                <div>No categories found. <a href="{{ route('admin.categories.create') }}" style="color:inherit;font-weight:700;">Create a category</a> before exporting.</div>
            </div>
        </div>
    </div>

    @endif

</div>{{-- end pt-export-page --}}

<script>
function performExport() {
    const exportBtn    = document.getElementById('exportBtn');
    const exportStatus = document.getElementById('exportStatus');
    const exportResult = document.getElementById('exportResult');
    const exportError  = document.getElementById('exportError');

    // Reset previous messages
    exportResult.style.display = 'none';
    exportError.style.display  = 'none';
    exportStatus.style.display = 'none';

    // Show loading state
    exportBtn.disabled = true;
    exportBtn.innerHTML = '<span class="pt-spinner"></span> Generating…';
    exportStatus.style.display = 'block';
    exportStatus.innerHTML = `
        <div class="pt-alert pt-alert-info">
            <i class="feather-loader"></i>
            <div>Processing your export request…</div>
        </div>`;

    // Collect filter values
    const params = new URLSearchParams();
    params.append('format', 'csv');
    const category    = document.getElementById('category').value;
    const minPrice    = document.getElementById('min_price').value;
    const maxPrice    = document.getElementById('max_price').value;
    const stockStatus = document.getElementById('stock_status').value;
    const dateFrom    = document.getElementById('date_from').value;
    const dateTo      = document.getElementById('date_to').value;

    if (category)    params.append('category',     category);
    if (minPrice)    params.append('min_price',     minPrice);
    if (maxPrice)    params.append('max_price',     maxPrice);
    if (stockStatus) params.append('stock_status',  stockStatus);
    if (dateFrom)    params.append('date_from',     dateFrom);
    if (dateTo)      params.append('date_to',       dateTo);

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        showError('Security token not found. Please refresh the page.');
        return;
    }

    fetch('{{ route("admin.export.books") }}?' + params.toString(), {
        method : 'GET',
        headers: {
            'X-CSRF-TOKEN'     : csrfMeta.content,
            'Accept'           : 'application/json',
            'X-Requested-With' : 'XMLHttpRequest',
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(d => { throw new Error(d.message || 'HTTP ' + response.status); });
        }
        return response.json();
    })
    .then(data => {
        exportStatus.style.display = 'none';

        if (data.success) {
            const count = data.rows_exported ?? '?';
            exportResult.style.display = 'block';
            exportResult.innerHTML = `
                <div class="pt-result">
                    <div class="pt-result-title">
                        <i class="feather-check-circle"></i> Export complete
                    </div>
                    <div class="pt-result-meta">
                        ${count} book${count !== 1 ? 's' : ''} exported
                        ${data.export_id ? ' &nbsp;·&nbsp; Export #' + data.export_id : ''}
                    </div>
                    <a href="${data.download_url}" target="_blank" class="pt-btn pt-btn-primary" style="font-size:.85rem;">
                        <i class="feather-download"></i> Download CSV
                    </a>
                </div>`;

            // Auto-trigger download
            window.open(data.download_url, '_blank');
        } else {
            showError(data.message || 'Export failed for an unknown reason.');
        }
    })
    .catch(error => {
        exportStatus.style.display = 'none';
        showError(error.message || 'An unexpected error occurred.');
    })
    .finally(() => {
        exportBtn.disabled = false;
        exportBtn.innerHTML = '<i class="feather-download"></i> Export Books';
    });
}

function showError(message) {
    const exportError = document.getElementById('exportError');
    exportError.style.display = 'block';
    exportError.innerHTML = `
        <div class="pt-alert pt-alert-danger">
            <i class="feather-alert-triangle"></i>
            <div><strong>Export failed</strong><br>${escHtml(message)}</div>
        </div>`;
}

function resetFilters() {
    ['category','stock_status','date_from','date_to'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('min_price').value = '';
    document.getElementById('max_price').value = '';
    document.getElementById('exportResult').style.display = 'none';
    document.getElementById('exportError').style.display  = 'none';
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}
</script>
@endsection