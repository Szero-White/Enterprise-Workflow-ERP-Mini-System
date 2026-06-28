@extends('layouts.app')
@section('content')<h2>Edit Role</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.roles.update', $role) }}">@csrf @method('PUT') @include('admin.roles._form')</form></div>@endsection
