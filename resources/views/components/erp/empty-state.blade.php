@props([
    'icon' => 'bi-inbox',
    'title',
    'description' => null,
])

<div class="erp-empty-state">
    <div class="erp-empty-state__icon"><i class="bi {{ $icon }}"></i></div>
    <div class="erp-empty-state__title">{{ $title }}</div>
    @if($description)
        <div class="erp-empty-state__description">{{ $description }}</div>
    @endif
    @isset($action)
        <div class="mt-3">{{ $action }}</div>
    @endisset
</div>
