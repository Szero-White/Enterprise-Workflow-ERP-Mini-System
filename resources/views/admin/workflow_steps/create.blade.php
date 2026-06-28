@extends('layouts.app')
@section('content')<h2>Add Workflow Step</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.workflow-templates.steps.store', $workflowTemplate) }}">@csrf @include('admin.workflow_steps._form')</form></div>@endsection
