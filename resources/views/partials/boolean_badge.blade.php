<span class="badge {{ ($value ?? false) ? 'text-bg-success' : 'text-bg-secondary' }} erp-status-badge">
    <i class="bi {{ ($value ?? false) ? 'bi-check-circle-fill' : 'bi-dash-circle' }}"></i>
    <span>{{ ($value ?? false) ? ($trueLabel ?? __('status.active')) : ($falseLabel ?? __('status.inactive')) }}</span>
</span>
