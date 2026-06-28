@extends('layouts.app')
@section('content')<h2>Edit Workflow Step</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.workflow-templates.steps.update', [$workflowTemplate, $step]) }}">@csrf @method('PUT') @include('admin.workflow_steps._form')</form></div>@endsection
