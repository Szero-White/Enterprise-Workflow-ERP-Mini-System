@extends('layouts.app')
@section('content')<h2>Create Role</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.roles.store') }}">@csrf @include('admin.roles._form')</form></div>@endsection
