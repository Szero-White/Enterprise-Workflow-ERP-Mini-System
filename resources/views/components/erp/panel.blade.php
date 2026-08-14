@props([
    'title' => null,
    'subtitle' => null,
    'class' => '',
    'flush' => false,
])

<section {{ $attributes->merge(['class' => 'erp-panel '.$class]) }}>
    @if($title || $subtitle || isset($actions))
        <header class="erp-panel__header">
            <div>
                @if($title)<h3 class="erp-panel__title">{{ $title }}</h3>@endif
                @if($subtitle)<p class="erp-panel__subtitle">{{ $subtitle }}</p>@endif
            </div>
            @isset($actions)<div class="erp-panel__actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div class="{{ $flush ? '' : 'erp-panel__body' }}">
        {{ $slot }}
    </div>
</section>
