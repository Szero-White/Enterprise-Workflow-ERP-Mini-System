@props(['status'])

<span class="badge {{ $status->badgeClass() }} d-inline-flex align-items-center gap-1">
    {{ $status->label() }}
    <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $status->tooltip() }}" aria-label="{{ $status->tooltip() }}"></i>
</span>
