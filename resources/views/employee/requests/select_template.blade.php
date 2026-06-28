@extends('layouts.app')
@section('content')
<h2 class="mb-3">Select Form Template</h2>
<div class="row g-3">
@foreach($templates as $template)
    <div class="col-md-4">
        <div class="content-card p-3 h-100">
            <h5>{{ $template->name }}</h5>
            <div class="text-muted small mb-2">{{ $template->code }} · {{ $template->fields_count }} fields</div>
            <p>{{ $template->description }}</p>
            <a href="{{ route('employee.requests.create', $template) }}" class="btn btn-primary">Create</a>
        </div>
    </div>
@endforeach
</div>
@endsection
