@extends('layouts.app')
@section('content')<h2>Edit User</h2><div class="content-card p-3"><form method="POST" action="{{ route('admin.users.update', $user) }}">@csrf @method('PUT') @include('admin.users._form')</form></div>@endsection
