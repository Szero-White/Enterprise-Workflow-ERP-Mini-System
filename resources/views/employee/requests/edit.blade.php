@extends('layouts.app')

@section('page_title', 'Edit Returned Request')
@section('page_eyebrow', 'Employee / Requests')

@section('content')
<div class="content-card p-4 p-lg-4 erp-form-card">
    <div class="mb-4">
        <h2 class="erp-section-title">Edit Returned Request: {{ $workflowRequest->request_code }}</h2>
        <p class="erp-section-subtitle mb-0">Update the required fields and resubmit the request for approval.</p>
    </div>

    <form method="POST" action="{{ route('employee.requests.update', $workflowRequest) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => $oldValues])
        @include('partials.form_actions', ['submitLabel' => 'Resubmit', 'cancelUrl' => route('employee.requests.show', $workflowRequest), 'cancelLabel' => 'Back'])
    </form>
</div>
@endsection
