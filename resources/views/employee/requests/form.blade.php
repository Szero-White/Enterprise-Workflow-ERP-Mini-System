@extends('layouts.app')
@section('content')
<h2>Create Request: {{ $formTemplate->name }}</h2>
<div class="content-card p-3">
<form method="POST" action="{{ route('employee.requests.store', $formTemplate) }}" enctype="multipart/form-data">
    @csrf
    @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => collect()])
    <button class="btn btn-primary">Submit Request</button>
    <a href="{{ route('employee.requests.select-template') }}" class="btn btn-light">Back</a>
</form>
</div>
@endsection
