@extends('layouts.app')
@section('content')
<h2>Create Department</h2>
<div class="content-card p-3"><form method="POST" action="{{ route('admin.departments.store') }}">@csrf @include('admin.departments._form')</form></div>
@endsection
