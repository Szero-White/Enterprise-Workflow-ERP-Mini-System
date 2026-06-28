@extends('layouts.app')
@section('content')<h2>Edit Field: {{ $formTemplate->name }}</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.form-templates.fields.update', [$formTemplate, $field]) }}">@csrf @method('PUT') @include('admin.form_fields._form')</form></div>@endsection
