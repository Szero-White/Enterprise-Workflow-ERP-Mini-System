<span class="badge {{ ($value ?? false) ? 'text-bg-success' : 'text-bg-secondary' }} rounded-pill px-3 py-2 fw-semibold">
    {{ ($value ?? false) ? ($trueLabel ?? __('status.active')) : ($falseLabel ?? __('status.inactive')) }}
</span>
