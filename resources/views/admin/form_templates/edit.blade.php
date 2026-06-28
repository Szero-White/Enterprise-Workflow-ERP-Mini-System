@extends('layouts.app')
@section('content')<h2>Edit Form Template</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.form-templates.update', $formTemplate) }}">@csrf @method('PUT') @include('admin.form_templates._form')</form></div>@endsection
