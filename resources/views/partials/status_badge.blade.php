@php
    $meta = \App\Models\WorkflowRequest::statusMeta($status ?? null);
@endphp

<span class="badge {{ $meta['class'] }} erp-status-badge">
    <i class="bi {{ $meta['icon'] }}"></i>
    <span>{{ $meta['label'] }}</span>
</span>
