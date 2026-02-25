@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert"
         style="border-left: 4px solid #198754; border-radius: 8px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert"
         style="border-left: 4px solid #dc3545; border-radius: 8px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('status'))
    <div class="alert alert-info alert-dismissible fade show mx-3 mt-3" role="alert"
         style="border-left: 4px solid #0dcaf0; border-radius: 8px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2"></i>
            <span>{{ session('status') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert"
         style="border-left: 4px solid #dc3545; border-radius: 8px;">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
        </div>
        <ul class="mb-0 ps-4 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif