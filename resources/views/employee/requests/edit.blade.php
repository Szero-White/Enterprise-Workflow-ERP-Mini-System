@extends('layouts.app')
@section('content')
<h2>Edit Returned Request: {{ $workflowRequest->request_code }}</h2>
<div class="content-card p-3">
<form method="POST" action="{{ route('employee.requests.update', $workflowRequest) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => $oldValues])
    <button class="btn btn-primary">Resubmit</button>
    <a href="{{ route('employee.requests.show', $workflowRequest) }}" class="btn btn-light">Back</a>
</form>
</div>
@endsection
