@extends('layouts.app')
@section('content')<h2>Create User</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.users.store') }}">@csrf @include('admin.users._form')</form></div>@endsection
