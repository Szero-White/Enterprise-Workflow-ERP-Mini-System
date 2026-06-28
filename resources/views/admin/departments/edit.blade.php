@extends('layouts.app')
@section('content')
<h2>Edit Department</h2>
<div class="content-card p-3"><form method="POST" action="{{ route('admin.departments.update', $department) }}">@csrf @method('PUT') @include('admin.departments._form')</form></div>
@endsection
