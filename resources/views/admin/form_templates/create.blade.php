@extends('layouts.app')
@section('content')<h2>Create Form Template</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.form-templates.store') }}">@csrf @include('admin.form_templates._form')</form></div>@endsection
