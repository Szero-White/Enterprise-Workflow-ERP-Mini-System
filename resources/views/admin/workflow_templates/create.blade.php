@extends('layouts.app')
@section('content')<h2>Create Workflow</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.workflow-templates.store') }}">@csrf @include('admin.workflow_templates._form')</form></div>@endsection
