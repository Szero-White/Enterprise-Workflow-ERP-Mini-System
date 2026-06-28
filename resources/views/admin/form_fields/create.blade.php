@extends('layouts.app')
@section('content')<h2>Add Field: {{ $formTemplate->name }}</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.form-templates.fields.store', $formTemplate) }}">@csrf @include('admin.form_fields._form')</form></div>@endsection
