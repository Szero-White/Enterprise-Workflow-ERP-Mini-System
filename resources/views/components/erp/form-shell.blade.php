@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => null,
    'cancelUrl',
    'cancelLabel' => null,
    'asideTitle' => null,
    'asideHint' => null,
])

<x-erp.page-header :title="$title" :eyebrow="$eyebrow" :description="$description" />

<form method="POST" action="{{ $action }}">
    @csrf
    @if(strtoupper($method) !== 'POST')
        @method($method)
    @endif

    <div class="erp-form-layout">
        <section class="erp-form-section">
            {{ $slot }}
        </section>

        <aside class="erp-form-aside">
            <div class="erp-form-actions-card">
                <div class="erp-form-actions-card__title">{{ $asideTitle ?? $title }}</div>
                @if($asideHint)
                    <div class="erp-form-actions-card__hint">{{ $asideHint }}</div>
                @endif
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-check2-circle"></i>{{ $submitLabel ?? __('ui.save') }}
                </button>
                <a href="{{ $cancelUrl }}" class="btn btn-light border w-100 mt-2">{{ $cancelLabel ?? __('ui.cancel') }}</a>
            </div>
        </aside>
    </div>
</form>
