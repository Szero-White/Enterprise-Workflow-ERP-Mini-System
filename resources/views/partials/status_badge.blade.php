@php
    $meta = \App\Models\WorkflowRequest::statusMeta($status ?? null);
@endphp

<span class="badge {{ $meta['class'] }} d-inline-flex align-items-center gap-1 rounded-pill px-3 py-2 fw-semibold">
    <i class="bi {{ $meta['icon'] }}"></i>
    <span>{{ $meta['label'] }}</span>
</span>
