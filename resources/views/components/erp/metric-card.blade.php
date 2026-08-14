@props([
    'label',
    'value',
    'icon' => 'bi-graph-up',
    'tone' => 'primary',
    'hint' => null,
])

<article class="erp-metric-card erp-metric-card--{{ $tone }}">
    <div class="erp-metric-card__top">
        <div class="erp-metric-card__icon" aria-hidden="true">
            <i class="bi {{ $icon }}"></i>
        </div>
        <span class="erp-metric-card__label">{{ $label }}</span>
    </div>
    <div class="erp-metric-card__value">{{ $value }}</div>
    @if($hint)
        <div class="erp-metric-card__hint">{{ $hint }}</div>
    @endif
</article>
