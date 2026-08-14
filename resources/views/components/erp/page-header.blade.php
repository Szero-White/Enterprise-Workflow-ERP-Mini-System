@props([
    'title',
    'eyebrow' => null,
    'description' => null,
])

<div class="erp-page-header">
    <div class="erp-page-header-copy">
        @if($eyebrow)
            <div class="erp-kicker">{{ $eyebrow }}</div>
        @endif
        <h2 class="erp-display-title">{{ $title }}</h2>
        @if($description)
            <p class="erp-display-description">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="erp-page-actions">
            {{ $actions }}
        </div>
    @endisset
</div>
