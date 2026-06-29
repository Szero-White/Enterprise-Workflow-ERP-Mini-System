@extends('layouts.app')

@section('page_title', 'Create Request')
@section('page_eyebrow', 'Employee / Requests')

@section('content')
<div class="mb-3">
    <h2 class="h4 mb-1">Select Form Template</h2>
    <p class="text-muted mb-0">Choose the template that matches the request you want to submit.</p>
</div>

<div class="row g-3">
@forelse($templates as $template)
    <div class="col-md-6 col-xl-4">
        <div class="content-card p-3 p-lg-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h5 class="mb-1">{{ $template->name }}</h5>
                    <div class="text-muted small">{{ $template->code }} · {{ $template->fields_count }} fields</div>
                </div>
                @include('partials.boolean_badge', ['value' => $template->is_active, 'trueLabel' => 'Active', 'falseLabel' => 'Inactive'])
            </div>

            <p class="text-muted flex-grow-1 mb-4">{{ $template->description ?: 'No description provided.' }}</p>

            <a href="{{ route('employee.requests.create', $template) }}" class="btn btn-primary rounded-3">Create Request</a>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="content-card p-5 text-center text-muted">No active form templates available.</div>
    </div>
@endforelse
</div>
@endsection
