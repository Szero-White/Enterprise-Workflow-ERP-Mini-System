@extends('layouts.app')
@section('content')<h2>Edit Workflow</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.workflow-templates.update', $workflowTemplate) }}">@csrf @method('PUT') @include('admin.workflow_templates._form')</form></div>@endsection
