@if (session('success'))
    <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-check-circle-fill mt-1"></i>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-start gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-4" role="alert">
        <div class="fw-semibold mb-2 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill"></i>
            <span>{{ __('messages.validation_failed') }}</span>
        </div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
