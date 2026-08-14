@extends('layouts.app')

@section('page_title', __('ui.edit_returned_request'))
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<div class="content-card p-4 p-lg-4 erp-form-card">
    <div class="mb-4">
        <h2 class="erp-section-title">{{ __('ui.edit_returned_request_for', ['code' => $workflowRequest->request_code]) }}</h2>
        <p class="erp-section-subtitle mb-0">{{ __('ui.edit_returned_request_description') }}</p>
    </div>

    <form method="POST" action="{{ route('employee.requests.update', $workflowRequest) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => $oldValues])
        @include('partials.form_actions', ['submitLabel' => __('ui.resubmit'), 'cancelUrl' => route('employee.requests.show', $workflowRequest), 'cancelLabel' => __('ui.back')])
    </form>
</div>
@endsection
