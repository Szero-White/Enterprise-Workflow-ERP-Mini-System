@extends('layouts.app')

@section('page_title', 'Create Request')
@section('page_eyebrow', 'Employee / Requests')

@section('content')
<div class="content-card p-4 p-lg-4 erp-form-card">
    <div class="mb-4">
        <h2 class="erp-section-title">Create Request: {{ $formTemplate->name }}</h2>
        <p class="erp-section-subtitle mb-0">Complete the dynamic form below and submit it into the approval workflow.</p>
    </div>

    <form method="POST" action="{{ route('employee.requests.store', $formTemplate) }}" enctype="multipart/form-data">
        @csrf
        @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => collect()])
        @include('partials.form_actions', ['submitLabel' => 'Submit Request', 'cancelUrl' => route('employee.requests.select-template'), 'cancelLabel' => 'Back'])
    </form>
</div>
@endsection
