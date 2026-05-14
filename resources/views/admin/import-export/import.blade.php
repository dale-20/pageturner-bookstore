@extends('layouts.admin-layout')

@section('title', 'Import Books - PageTurner')

@push('styles')
<style>
    /* ── PageTurner Import – dark-mode-aware editorial skin ── */
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

        /* alert colours */
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

    /* ── Dark mode overrides (matches your admin layout's .dark class) ── */
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
    .pt-import-page {
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

    /* ── Instructions banner ── */
    .pt-instructions {
        background: var(--pt-alert-info-bg);
        border-left: 4px solid var(--pt-alert-info-border);
        border-radius: 0 var(--pt-radius) var(--pt-radius) 0;
        padding: .85rem 1.1rem;
        margin-bottom: 1.5rem;
        font-family: sans-serif;
        font-size: .84rem;
        color: var(--pt-alert-info-color);
    }
    .pt-instructions strong { display: block; margin-bottom: .4rem; font-size: .9rem; }
    .pt-instructions ul { margin: 0; padding-left: 1.2rem; }
    .pt-instructions li { margin-bottom: .25rem; }
    .pt-instructions .pt-req { font-weight: 600; color: var(--pt-accent); }

    /* ── Drop zone ── */
    .pt-drop-zone {
        border: 2px dashed var(--pt-border);
        border-radius: var(--pt-radius);
        padding: 2rem 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: var(--pt-paper);
        position: relative;
    }
    .pt-drop-zone:hover,
    .pt-drop-zone.drag-over {
        border-color: var(--pt-ink);
        background: var(--pt-paper);
        filter: brightness(.96);
    }
    .pt-drop-zone.has-file {
        border-color: var(--pt-success);
        background: var(--pt-alert-ok-bg);
    }
    .pt-drop-zone.has-error {
        border-color: var(--pt-accent);
        background: var(--pt-alert-err-bg);
    }
    .pt-drop-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .pt-drop-zone .pt-dz-icon { font-size: 2rem; color: var(--pt-muted); margin-bottom: .5rem; }
    .pt-drop-zone .pt-dz-label {
        font-family: sans-serif;
        font-size: .9rem;
        color: var(--pt-ink);
        font-weight: 600;
    }
    .pt-drop-zone .pt-dz-sub {
        font-family: sans-serif;
        font-size: .78rem;
        color: var(--pt-muted);
        margin-top: .25rem;
    }
    .pt-file-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        background: var(--pt-surface);
        border: 1px solid var(--pt-success);
        color: var(--pt-success);
        border-radius: 20px;
        padding: .25rem .75rem;
        font-size: .8rem;
        font-family: sans-serif;
        font-weight: 600;
        margin-top: .5rem;
    }
    .pt-file-chip i { font-size: .85rem; }

    /* ── Toggle row ── */
    .pt-toggle-row {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .85rem 1rem;
        background: var(--pt-paper);
        border-radius: var(--pt-radius);
        border: 1px solid var(--pt-border);
        margin-bottom: 1.25rem;
        transition: border-color .2s;
    }
    .pt-toggle-row.is-active {
        border-color: var(--pt-success);
        background: var(--pt-alert-ok-bg);
    }
    .pt-toggle-row .pt-toggle-info { flex: 1; }
    .pt-toggle-row .pt-toggle-label {
        font-family: sans-serif;
        font-size: .9rem;
        font-weight: 600;
        color: var(--pt-ink);
        margin-bottom: .15rem;
    }
    .pt-toggle-row .pt-toggle-sub {
        font-family: sans-serif;
        font-size: .78rem;
        color: var(--pt-muted);
    }
    /* Make the Bootstrap switch thumb visible in dark mode */
    .dark .form-check-input { background-color: var(--pt-border); border-color: var(--pt-border); }
    .dark .form-check-input:checked { background-color: var(--pt-success); border-color: var(--pt-success); }

    /* ── Action buttons ── */
    .pt-actions {
        display: flex;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
        margin-top: 1.25rem;
    }
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
    .pt-btn-primary  { background: var(--pt-ink); color: var(--pt-surface); }
    .pt-btn-outline  { background: transparent; border: 1.5px solid var(--pt-border); color: var(--pt-ink); }
    .pt-btn-danger   { background: var(--pt-accent); color: #fff; }
    .pt-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    /* ── Progress section ── */
    #importProgress { display: none; margin-top: 1.5rem; }

    .pt-stat-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: .75rem;
        margin-bottom: 1rem;
    }
    .pt-stat {
        background: var(--pt-paper);
        border: 1px solid var(--pt-border);
        border-radius: var(--pt-radius);
        padding: .75rem;
        text-align: center;
    }
    .pt-stat .pt-stat-label {
        font-family: sans-serif;
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--pt-muted);
        margin-bottom: .2rem;
    }
    .pt-stat .pt-stat-val {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--pt-ink);
        line-height: 1;
    }
    .pt-stat.stat-success .pt-stat-val { color: var(--pt-success); }
    .pt-stat.stat-skip    .pt-stat-val { color: var(--pt-warn); }

    .pt-progress-wrap { margin-bottom: .75rem; }
    .pt-progress-bar-track {
        height: 8px;
        background: var(--pt-border);
        border-radius: 99px;
        overflow: hidden;
    }
    .pt-progress-bar-fill {
        height: 100%;
        background: var(--pt-ink);
        border-radius: 99px;
        width: 0%;
        transition: width .3s ease;
    }
    .pt-progress-bar-fill.fill-success { background: var(--pt-success); }
    .pt-progress-label {
        font-family: sans-serif;
        font-size: .75rem;
        color: var(--pt-muted);
        text-align: right;
        margin-top: .3rem;
    }

    /* ── Status alerts ── */
    .pt-alert {
        border-radius: var(--pt-radius);
        padding: .85rem 1rem;
        font-family: sans-serif;
        font-size: .875rem;
        display: flex;
        gap: .6rem;
        align-items: flex-start;
        margin-bottom: .75rem;
    }
    .pt-alert i { margin-top: .1rem; flex-shrink: 0; }
    .pt-alert-info    { background: var(--pt-alert-info-bg);  border: 1px solid var(--pt-alert-info-border);  color: var(--pt-alert-info-color); }
    .pt-alert-success { background: var(--pt-alert-ok-bg);    border: 1px solid var(--pt-alert-ok-border);    color: var(--pt-alert-ok-color); }
    .pt-alert-warning { background: var(--pt-alert-warn-bg);  border: 1px solid var(--pt-alert-warn-border);  color: var(--pt-alert-warn-color); }
    .pt-alert-danger  { background: var(--pt-alert-err-bg);   border: 1px solid var(--pt-alert-err-border);   color: var(--pt-alert-err-color); }

    /* ── Failures / skips table ── */
    .pt-failures { margin-top: 1rem; }
    .pt-failures-head {
        font-family: sans-serif;
        font-size: .82rem;
        font-weight: 700;
        color: var(--pt-accent);
        margin-bottom: .5rem;
        display: flex;
        align-items: center;
        gap: .35rem;
    }
    .pt-failures-scroll {
        max-height: 280px;
        overflow-y: auto;
        border: 1px solid var(--pt-border);
        border-radius: 6px;
    }
    .pt-failures-scroll table {
        width: 100%;
        border-collapse: collapse;
        font-family: sans-serif;
        font-size: .8rem;
    }
    .pt-failures-scroll thead th {
        background: var(--pt-alert-err-bg);
        color: var(--pt-accent);
        padding: .45rem .75rem;
        text-align: left;
        font-weight: 700;
        position: sticky;
        top: 0;
    }
    .pt-failures-scroll tbody tr:nth-child(even) { background: var(--pt-paper); }
    .pt-failures-scroll tbody td {
        padding: .4rem .75rem;
        vertical-align: top;
        color: var(--pt-ink);
        border-top: 1px solid var(--pt-border);
    }
    .pt-failures-scroll tbody td:first-child {
        white-space: nowrap;
        color: var(--pt-accent);
        font-weight: 600;
    }

    /* ── Recent imports table ── */
    .pt-history-table {
        width: 100%;
        border-collapse: collapse;
        font-family: sans-serif;
        font-size: .84rem;
    }
    .pt-history-table th {
        text-align: left;
        padding: .55rem .75rem;
        color: var(--pt-muted);
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid var(--pt-border);
        font-weight: 600;
        background: var(--pt-paper);
    }
    .pt-history-table td {
        padding: .6rem .75rem;
        border-bottom: 1px solid var(--pt-border);
        vertical-align: middle;
        color: var(--pt-ink);
    }
    .pt-history-table tr:last-child td { border-bottom: none; }
    .pt-history-table tr:hover td { background: var(--pt-paper); }

    .pt-badge {
        display: inline-block;
        padding: .2rem .55rem;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
        font-family: sans-serif;
    }
    .badge-completed  { background: var(--pt-alert-ok-bg);   color: var(--pt-success); }
    .badge-processing { background: var(--pt-alert-warn-bg); color: var(--pt-warn); }
    .badge-failed     { background: var(--pt-alert-err-bg);  color: var(--pt-accent); }

    .pt-file-name {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
    }

    /* ── Divider ── */
    .pt-hr { border: none; border-top: 1px solid var(--pt-border); margin: 1.5rem 0 1rem; }

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
    .pt-spinner-dark {
        border-color: rgba(26,26,46,.2);
        border-top-color: var(--pt-ink);
    }
    .dark .pt-spinner-dark {
        border-color: rgba(255,255,255,.2);
        border-top-color: var(--pt-ink);
    }
</style>
@endpush

@section('content')
<div class="pt-import-page">

    {{-- Page Header --}}
    <div class="pt-page-header">
        <div class="pt-icon-wrap"><i class="feather-upload-cloud"></i></div>
        <div>
            <h1>Import Books</h1>
            <p>Upload a CSV file to add or update books in bulk</p>
        </div>
        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('admin.import.logs') }}" class="pt-btn pt-btn-outline">
                <i class="feather-list"></i> View Logs
            </a>
            <a href="{{ route('admin.export.template') }}" class="pt-btn pt-btn-outline">
                <i class="feather-download"></i> Template
            </a>
        </div>
    </div>

    {{-- Import Card --}}
    <div class="pt-card">
        <div class="pt-card-header">
            <h2><i class="feather-file-text me-2"></i>Upload File</h2>
        </div>
        <div class="pt-card-body">

            {{-- Instructions --}}
            <div class="pt-instructions">
                <strong><i class="feather-info" style="font-size:.9rem;"></i> &nbsp;Before you import</strong>
                <ul>
                    <li>Accepted format: <span class="pt-req">.csv only</span> &nbsp;·&nbsp; Max size: <span class="pt-req">10 MB</span></li>
                    <li>Required columns: <span class="pt-req">ISBN, Title, Price</span> &nbsp;·&nbsp; Optional: Author, Stock, Category, Description</li>
                    <li>ISBN must be ISBN-10 or ISBN-13 (dashes/spaces stripped automatically)</li>
                    <li>Price must be a positive number up to 9 999.99 &nbsp;·&nbsp; Stock must be ≥ 0</li>
                    <li>Categories are <strong>created automatically</strong> if they don't exist yet</li>
                    <li><strong>Update existing</strong> is <span class="pt-req">ON by default</span> — books with a matching ISBN will be updated, not skipped</li>
                </ul>
            </div>

            <form id="importForm" enctype="multipart/form-data">
                @csrf

                {{-- Drop zone --}}
                <div class="pt-drop-zone mb-3" id="dropZone">
                    <input type="file" name="file" id="importFile" accept=".csv" required>
                    <div class="pt-dz-icon"><i class="feather-upload-cloud"></i></div>
                    <div class="pt-dz-label" id="dzLabel">Drag & drop your CSV file here, or click to browse</div>
                    <div class="pt-dz-sub" id="dzSub">Only .csv files up to 10 MB</div>
                    <div id="fileChip" style="display:none;">
                        <span class="pt-file-chip"><i class="feather-check"></i> <span id="fileName"></span></span>
                    </div>
                    <div id="fileErrorMsg" style="display:none; margin-top:.5rem;">
                        <span class="pt-file-chip" style="border-color:var(--pt-accent);color:var(--pt-accent);">
                            <i class="feather-alert-circle"></i> <span id="fileErrorText"></span>
                        </span>
                    </div>
                </div>

                {{--
                    FIX: update_existing defaults to CHECKED so books with duplicate ISBNs
                    are updated instead of being silently skipped/failed.
                    Users who want insert-only can uncheck it deliberately.
                --}}
                <div class="pt-toggle-row is-active" id="updateToggleRow">
                    <div class="pt-toggle-info">
                        <div class="pt-toggle-label">Update existing books</div>
                        <div class="pt-toggle-sub" id="updateToggleSub">
                            Books matching an existing ISBN will be <strong>updated</strong> — turn off to skip duplicates instead
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0 ms-2 mt-1">
                        <input class="form-check-input" type="checkbox" name="update_existing"
                               id="updateExisting" value="1" role="switch" checked>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="pt-actions">
                    <button type="submit" class="pt-btn pt-btn-primary" id="importBtn">
                        <i class="feather-upload"></i> Start Import
                    </button>
                    <button type="button" class="pt-btn pt-btn-danger" id="cancelBtn"
                            style="display:none;" onclick="cancelImport()">
                        <i class="feather-x"></i> Cancel
                    </button>
                </div>
            </form>

            {{-- ── Progress ── --}}
            <div id="importProgress">
                <hr class="pt-hr">

                <div class="pt-stat-row">
                    <div class="pt-stat">
                        <div class="pt-stat-label">Total Rows</div>
                        <div class="pt-stat-val" id="totalRows">—</div>
                    </div>
                    <div class="pt-stat stat-success">
                        <div class="pt-stat-label">Imported</div>
                        <div class="pt-stat-val" id="successfulRows">0</div>
                    </div>
                    <div class="pt-stat stat-skip">
                        <div class="pt-stat-label">Skipped / Failed</div>
                        <div class="pt-stat-val" id="failedRows">0</div>
                    </div>
                </div>

                <div class="pt-progress-wrap">
                    <div class="pt-progress-bar-track">
                        <div class="pt-progress-bar-fill" id="progressFill"></div>
                    </div>
                    <div class="pt-progress-label" id="progressLabel">0%</div>
                </div>

                <div id="importStatusArea"></div>

                <div id="failureList" style="display:none;" class="pt-failures">
                    <div class="pt-failures-head"><i class="feather-alert-triangle"></i> Skipped / Failed Rows</div>
                    <div class="pt-failures-scroll">
                        <table>
                            <thead>
                                <tr><th style="width:60px;">Row</th><th>Reason</th></tr>
                            </thead>
                            <tbody id="failureTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Recent Imports --}}
    <div class="pt-card">
        <div class="pt-card-header">
            <h2><i class="feather-clock me-2"></i>Recent Imports</h2>
            <button class="pt-btn pt-btn-outline" style="padding:.3rem .75rem; font-size:.78rem;"
                    onclick="loadRecentImports()">
                <i class="feather-refresh-cw"></i> Refresh
            </button>
        </div>
        <div class="pt-card-body" style="padding: 0;">
            <div style="overflow-x:auto;">
                <table class="pt-history-table">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Status</th>
                            <th>Results</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="recentImports">
                        <tr><td colspan="4" style="text-align:center; color:var(--pt-muted); padding:1.5rem;">
                            Loading&hellip;
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>{{-- end pt-import-page --}}

<script>
/* ─────────────────────────────────────────────
   PageTurner Import Logic
   ───────────────────────────────────────────── */
let importInterval   = null;
let currentImportId  = null;
let pollingAttempts  = 0;
const MAX_POLLS      = 300;
const POLL_INTERVAL  = 1200;

/* ── Toggle visual feedback ── */
document.getElementById('updateExisting').addEventListener('change', function () {
    const row = document.getElementById('updateToggleRow');
    const sub = document.getElementById('updateToggleSub');
    if (this.checked) {
        row.classList.add('is-active');
        sub.innerHTML = 'Books matching an existing ISBN will be <strong>updated</strong> — turn off to skip duplicates instead';
    } else {
        row.classList.remove('is-active');
        sub.innerHTML = 'Books matching an existing ISBN will be <strong>skipped</strong> — only new ISBNs will be inserted';
    }
});

/* ── Drop zone ── */
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('importFile');

['dragenter','dragover'].forEach(ev =>
    dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.add('drag-over'); })
);
['dragleave','drop'].forEach(ev =>
    dropZone.addEventListener(ev, e => { e.preventDefault(); dropZone.classList.remove('drag-over'); })
);
dropZone.addEventListener('drop', e => {
    const f = e.dataTransfer.files[0];
    if (f) {
        const dt = new DataTransfer();
        dt.items.add(f);
        fileInput.files = dt.files;
        fileInput.dispatchEvent(new Event('change'));
    }
});

fileInput.addEventListener('change', function () {
    const file   = this.files[0];
    const chip   = document.getElementById('fileChip');
    const errMsg = document.getElementById('fileErrorMsg');
    const btn    = document.getElementById('importBtn');

    chip.style.display = 'none';
    errMsg.style.display = 'none';
    dropZone.classList.remove('has-file','has-error');
    btn.disabled = false;

    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.csv')) {
        showFileError('Only .csv files are allowed.');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        showFileError(`File too large (${(file.size/1048576).toFixed(1)} MB). Max 10 MB.`);
        return;
    }

    document.getElementById('fileName').textContent = file.name + ' — ' + (file.size/1024).toFixed(1) + ' KB';
    chip.style.display = 'block';
    dropZone.classList.add('has-file');
});

function showFileError(msg) {
    document.getElementById('fileErrorText').textContent = msg;
    document.getElementById('fileErrorMsg').style.display = 'block';
    dropZone.classList.add('has-error');
    document.getElementById('importBtn').disabled = true;
}

/* ── Form submit ── */
document.getElementById('importForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const importBtn   = document.getElementById('importBtn');
    const cancelBtn   = document.getElementById('cancelBtn');
    const progressDiv = document.getElementById('importProgress');
    const failureList = document.getElementById('failureList');

    failureList.style.display = 'none';
    document.getElementById('failureTableBody').innerHTML = '';
    document.getElementById('importStatusArea').innerHTML = '';
    setProgress(0);

    importBtn.disabled = true;
    importBtn.innerHTML = '<span class="pt-spinner"></span> Uploading…';
    cancelBtn.style.display = 'inline-flex';
    progressDiv.style.display = 'block';
    pollingAttempts = 0;

    setStatusAlert('info', 'feather-loader', 'Uploading file and processing rows…');

    try {
        const formData = new FormData(this);
        const response = await fetch('{{ route("admin.import.books") }}', {
            method : 'POST',
            body   : formData,
            headers: {
                'X-CSRF-TOKEN'     : document.querySelector('meta[name="csrf-token"]').content,
                'Accept'           : 'application/json',
                'X-Requested-With' : 'XMLHttpRequest',
            }
        });

        const data = await response.json();
        console.log('Import response:', data);

        if (data.success) {
            currentImportId = data.import_id;
            updateStats(data.total_rows, data.successful_rows, data.failed_rows);

            if (data.status === 'completed' || data.status === 'failed') {
                handleImportComplete(data);
            } else {
                importBtn.innerHTML = '<span class="pt-spinner"></span> Processing…';
                pollImportStatus(data.import_id);
            }
        } else {
            setStatusAlert('danger', 'feather-alert-circle', data.message || 'Import failed. Please try again.');
            resetImportButton();
        }

    } catch (err) {
        console.error('Import error:', err);
        setStatusAlert('danger', 'feather-alert-circle', 'Network error: ' + err.message);
        resetImportButton();
    }
});

/* ── Poll status (fallback for queued jobs) ── */
function pollImportStatus(importId) {
    if (importInterval) clearInterval(importInterval);

    importInterval = setInterval(async () => {
        pollingAttempts++;

        if (pollingAttempts > MAX_POLLS) {
            clearInterval(importInterval);
            importInterval = null;
            setStatusAlert('warning', 'feather-clock',
                'Import is taking longer than expected. Check Recent Imports below for updates.');
            resetImportButton();
            loadRecentImports();
            return;
        }

        try {
            const r    = await fetch('{{ url("admin/import/status") }}/' + importId, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!r.ok) return;
            const data = await r.json();

            updateStats(data.total_rows, data.successful_rows, data.failed_rows);
            if (data.failures && data.failures.length) showFailures(data.failures);

            if (data.status === 'completed' || data.status === 'failed') {
                clearInterval(importInterval);
                importInterval = null;
                handleImportComplete(data);
            }
        } catch (err) {
            console.warn('Poll error (continuing):', err);
        }
    }, POLL_INTERVAL);
}

/* ── Completion handler ── */
function handleImportComplete(data) {
    if (importInterval) { clearInterval(importInterval); importInterval = null; }
    resetImportButton();

    const success = data.successful_rows || 0;
    const failed  = data.failed_rows    || 0;
    const total   = data.total_rows     || 0;

    updateStats(total, success, failed);
    const pct = total > 0 ? Math.round((success / total) * 100) : 0;
    setProgress(pct, success === total);

    if (data.status === 'completed' && success > 0) {
        if (failed === 0) {
            setStatusAlert('success', 'feather-check-circle',
                `All <strong>${success}</strong> books imported successfully.`);
        } else {
            setStatusAlert('warning', 'feather-alert-triangle',
                `Import complete — <strong>${success}</strong> imported, <strong>${failed}</strong> skipped or failed.`);
        }
    } else {
        setStatusAlert('danger', 'feather-x-circle',
            `Import failed — ${failed} row${failed !== 1 ? 's' : ''} could not be processed. Check the list below.`);
    }

    if (data.failures && data.failures.length) showFailures(data.failures);
    loadRecentImports();
}

/* ── UI helpers ── */
function updateStats(total, success, failed) {
    document.getElementById('totalRows').textContent      = total   ?? '—';
    document.getElementById('successfulRows').textContent = success ?? 0;
    document.getElementById('failedRows').textContent     = failed  ?? 0;

    const processed = (success || 0) + (failed || 0);
    const pct = total > 0 ? Math.round((processed / total) * 100) : 0;
    setProgress(pct);
}

function setProgress(pct, success = false) {
    const fill = document.getElementById('progressFill');
    fill.style.width = pct + '%';
    fill.classList.toggle('fill-success', success);
    document.getElementById('progressLabel').textContent = pct + '%';
}

function setStatusAlert(type, icon, html) {
    document.getElementById('importStatusArea').innerHTML = `
        <div class="pt-alert pt-alert-${type}">
            <i class="${icon}"></i>
            <div>${html}</div>
        </div>`;
}

function showFailures(failures) {
    const list  = document.getElementById('failureList');
    const tbody = document.getElementById('failureTableBody');
    list.style.display = 'block';

    const max  = 20;
    const show = failures.slice(0, max);
    tbody.innerHTML = show.map(f => `
        <tr>
            <td>Row ${escHtml(String(f.row || '?'))}</td>
            <td>${escHtml(f.error || 'Unknown error')}</td>
        </tr>`).join('');

    if (failures.length > max) {
        tbody.innerHTML += `<tr><td colspan="2" style="text-align:center;color:var(--pt-muted);padding:.6rem;">
            …and ${failures.length - max} more — check server logs for the full list.</td></tr>`;
    }
}

function resetImportButton() {
    const btn = document.getElementById('importBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="feather-upload"></i> Start Import';
    document.getElementById('cancelBtn').style.display = 'none';
}

function cancelImport() {
    if (importInterval) { clearInterval(importInterval); importInterval = null; }
    setStatusAlert('warning', 'feather-slash',
        'Monitoring cancelled. The import continues in the background — check Recent Imports below.');
    resetImportButton();
    setTimeout(loadRecentImports, 2000);
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

/* ── Recent imports table ── */
function loadRecentImports() {
    const tbody = document.getElementById('recentImports');
    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--pt-muted);padding:1.2rem;">
        <span class="pt-spinner pt-spinner-dark" style="vertical-align:middle;"></span> Loading…</td></tr>`;

    fetch('{{ route("admin.import.recent") }}')
        .then(r => r.json())
        .then(data => {
            if (data.imports && data.imports.length > 0) {
                tbody.innerHTML = data.imports.map(imp => {
                    const statusMap = { completed: 'completed', processing: 'processing', failed: 'failed' };
                    const cls  = statusMap[imp.status] || 'processing';
                    const pct  = imp.total_rows > 0
                        ? Math.round(((imp.successful_rows || 0) / imp.total_rows) * 100) : 0;
                    const date = new Date(imp.created_at).toLocaleString('en-PH', {
                        month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    return `
                    <tr>
                        <td><span class="pt-file-name" title="${escHtml(imp.file_name)}">${escHtml(imp.file_name)}</span></td>
                        <td><span class="pt-badge badge-${cls}">${imp.status}</span></td>
                        <td style="font-size:.78rem;">
                            <span style="color:var(--pt-success);font-weight:600;">${imp.successful_rows || 0} ok</span>
                            ${imp.failed_rows > 0 ? ` &nbsp;·&nbsp; <span style="color:var(--pt-accent);font-weight:600;">${imp.failed_rows} skipped</span>` : ''}
                            <span style="color:var(--pt-muted);"> / ${imp.total_rows || 0} total</span>
                        </td>
                        <td style="font-size:.78rem; color:var(--pt-muted);">${date}</td>
                    </tr>`;
                }).join('');
            } else {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--pt-muted);padding:1.5rem;">
                    No imports yet</td></tr>`;
            }
        })
        .catch(() => {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--pt-accent);padding:1.2rem;">
                Failed to load recent imports</td></tr>`;
        });
}

document.addEventListener('DOMContentLoaded', loadRecentImports);
</script>
@endsection