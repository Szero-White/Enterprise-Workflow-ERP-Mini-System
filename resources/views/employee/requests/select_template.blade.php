@extends('layouts.app')

@section('page_title', __('menu.create_request'))
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<x-erp.page-header :title="__('ui.select_form_template')" :eyebrow="__('ui.my_requests_eyebrow')" :description="__('ui.select_form_template_description')" />

<div class="row g-3 erp-template-grid">
@forelse($templates as $template)
    <div class="col-lg-6">
        <article class="content-card erp-template-card h-100">
            <div class="erp-template-card__header">
                <div class="erp-template-card__identity">
                    <span class="erp-template-card__icon" aria-hidden="true">
                        <i class="bi bi-file-earmark-text"></i>
                    </span>
                    <div class="min-w-0">
                        <h5 class="erp-template-card__title text-truncate">{{ $template->name }}</h5>
                        <div class="erp-template-card__meta">
                            <span class="erp-record-code">{{ $template->code }}</span>
                            <span aria-hidden="true">·</span>
                            <span>{{ __('ui.field_count', ['count' => $template->fields_count]) }}</span>
                        </div>
                    </div>
                </div>
                @include('partials.boolean_badge', ['value' => $template->is_active])
            </div>

            <p class="erp-template-card__description">{{ $template->description ?: __('ui.no_description') }}</p>

            <div class="erp-template-card__footer">
                <a href="{{ route('employee.requests.create', $template) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i>
                    {{ __('ui.create_request') }}
                </a>
            </div>
        </article>
    </div>
@empty
    <div class="col-12">
        <x-erp.empty-state icon="bi-file-earmark-text" :title="__('ui.no_active_form_templates')" />
    </div>
@endforelse
</div>
@endsection
