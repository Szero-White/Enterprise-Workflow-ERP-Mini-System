@extends('layouts.app')

@section('page_title', __('menu.create_request'))
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<x-erp.page-header :title="__('ui.select_form_template')" :eyebrow="__('ui.my_requests_eyebrow')" :description="__('ui.select_form_template_description')" />

<div class="row g-3">
@forelse($templates as $template)
    <div class="col-md-6 col-xl-4">
        <div class="content-card p-3 p-lg-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h5 class="mb-1">{{ $template->name }}</h5>
                    <div class="text-muted small">{{ $template->code }} &middot; {{ __('ui.field_count', ['count' => $template->fields_count]) }}</div>
                </div>
                @include('partials.boolean_badge', ['value' => $template->is_active])
            </div>

            <p class="text-muted flex-grow-1 mb-4">{{ $template->description ?: __('ui.no_description') }}</p>

            <a href="{{ route('employee.requests.create', $template) }}" class="btn btn-primary rounded-3">{{ __('ui.create_request') }}</a>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="content-card p-5 text-center text-muted">{{ __('ui.no_active_form_templates') }}</div>
    </div>
@endforelse
</div>
@endsection
