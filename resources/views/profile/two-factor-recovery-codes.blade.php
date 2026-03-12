{{--
    resources/views/profile/two-factor-recovery-codes.blade.php
    Displays the user's 8 backup recovery codes after enabling 2FA.
--}}

@extends(auth()->user()->isAdmin() ? 'layouts.admin-layout' : 'layouts.app')

@section('title', 'Recovery Codes - PageTurner')
@if(auth()->user()->isAdmin())
    @section('page-title', 'Profile')
    @section('breadcrumb', '2FA Recovery Codes')
@endif

@section('content')
<div class="container py-5">

    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-4 mb-3" style="border-width: 2px;">
                <i class="bi bi-arrow-left me-2"></i>Back to Profile
            </a>
            <h2 class="fw-bold mb-1">Recovery Codes</h2>
            <p class="text-muted mb-0">Use these if you ever lose access to your authentication method.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            {{-- Warning Banner --}}
            <div class="alert rounded-4 mb-4 d-flex align-items-start gap-3"
                 style="background: rgba(245,158,11,0.08); border: 2px solid rgba(245,158,11,0.3); color: #92400e;">
                <i class="bi bi-exclamation-triangle-fill fs-5 mt-1" style="color: #f59e0b; flex-shrink: 0;"></i>
                <div>
                    <strong class="d-block mb-1">Store these codes somewhere safe.</strong>
                    <span class="small">Each code can only be used once. If you lose access to your authenticator and run out of codes, you will be locked out of your account.</span>
                </div>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 20px; background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #dc2626, #ef4444); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-key-fill text-white fs-5"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0" style="color: #1e293b;">Your Recovery Codes</h5>
                                <p class="text-muted small mb-0">{{ count($codes) }} code{{ count($codes) !== 1 ? 's' : '' }} remaining</p>
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" style="border-width: 2px;" onclick="copyAllCodes()" id="copyAllBtn">
                            <i class="bi bi-clipboard me-1"></i>Copy All
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="p-3 rounded-4" style="background: #0f172a;" id="codesContainer">
                        <div class="row g-2">
                            @foreach($codes as $code)
                                <div class="col-6">
                                    <code class="recovery-code d-block text-center py-2 px-3 rounded-3 fw-bold"
                                          style="background: rgba(255,255,255,0.05); color: #86efac; letter-spacing: 2px; font-size: 0.95rem; border: 1px solid rgba(255,255,255,0.08);">
                                        {{ $code }}
                                    </code>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" style="border-width: 2px;" onclick="printCodes()">
                            <i class="bi bi-printer me-2"></i>Print
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4" style="border-width: 2px;" onclick="downloadCodes()">
                            <i class="bi bi-download me-2"></i>Download
                        </button>
                    </div>
                </div>

                <div class="card-footer bg-transparent border-0 px-4 pb-4">
                    <div class="p-3 rounded-4" style="background: rgba(220,38,38,0.05); border: 1.5px solid rgba(220,38,38,0.15);">
                        <p class="text-muted small mb-0">
                            <i class="bi bi-info-circle-fill text-danger me-2"></i>
                            Once a recovery code is used, it's removed from this list. Generate new ones if you're running low.
                        </p>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('profile.edit') }}" class="btn btn-danger px-5 py-3 rounded-pill shadow-lg"
                   style="background: linear-gradient(135deg, #dc2626, #ef4444); border: none;">
                    <i class="bi bi-check-lg me-2"></i>Done — Go to Profile
                </a>
            </div>

        </div>
    </div>
</div>

{{-- Toast Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 9999;">
    @if(session('status'))
    <div id="toastSuccess" class="toast align-items-center border-0 text-white show" role="alert"
         style="background: linear-gradient(135deg, #16a34a, #22c55e); border-radius: 16px; min-width: 300px; box-shadow: 0 8px 24px rgba(22,163,74,0.35);">
        <div class="d-flex align-items-center p-3">
            <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center me-3"
                 style="width: 40px; height: 40px; flex-shrink: 0;">
                <i class="bi bi-check-lg fs-5"></i>
            </div>
            <div class="me-auto">
                <strong class="d-block">2FA Enabled!</strong>
                <small>{{ session('status') }}</small>
            </div>
            <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-progress" style="height: 4px; background: rgba(255,255,255,0.4); border-radius: 0 0 16px 16px; overflow: hidden;">
            <div id="toastSuccessBar" style="height: 100%; width: 100%; background: rgba(255,255,255,0.8); transition: width linear;"></div>
        </div>
    </div>
    @endif
</div>

<style>
    .btn { font-weight: 600; letter-spacing: 0.3px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4); }
    .btn-outline-secondary { border-width: 2px; color: #64748b; border-color: #e2e8f0; }
    .btn-outline-secondary:hover { background: #f1f5f9; border-color: #94a3b8; color: #334155; transform: translateY(-2px); }
    @media print {
        body * { visibility: hidden; }
        #codesContainer, #codesContainer * { visibility: visible; }
        #codesContainer { position: absolute; left: 0; top: 0; width: 100%; background: white !important; }
        .recovery-code { color: #000 !important; background: #f1f5f9 !important; border: 1px solid #ccc !important; }
    }
</style>

<script>
    const codes = @json($codes);

    function copyAllCodes() {
        navigator.clipboard.writeText(codes.join('\n')).then(() => {
            const btn = document.getElementById('copyAllBtn');
            btn.innerHTML = '<i class="bi bi-clipboard-check me-1"></i>Copied!';
            btn.style.color = '#16a34a';
            btn.style.borderColor = '#16a34a';
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-clipboard me-1"></i>Copy All';
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2500);
        });
    }

    function printCodes() {
        window.print();
    }

    function downloadCodes() {
        const content = [
            'PageTurner - 2FA Recovery Codes',
            'Generated: ' + new Date().toLocaleString(),
            '---',
            ...codes
        ].join('\n');
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'pageturner-recovery-codes.txt';
        a.click();
        URL.revokeObjectURL(url);
    }

    // Auto-show toast
    const toastEl = document.getElementById('toastSuccess');
    const bar = document.getElementById('toastSuccessBar');
    if (toastEl && bar) {
        const toast = new bootstrap.Toast(toastEl, { autohide: false });
        toast.show();
        bar.style.transition = 'width 6000ms linear';
        setTimeout(() => bar.style.width = '0%', 50);
        setTimeout(() => toast.hide(), 6000);
    }
</script>
@endsection