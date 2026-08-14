@extends('layouts.app')
@section('page_title', __('catalog.category.create_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4">
    <form method="POST" action="{{ route('catalog.categories.store') }}">
        @csrf
        @include('catalog.categories._form')
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary">{{ __('catalog.category.save') }}</button>
            <a href="{{ route('catalog.categories.index') }}" class="btn btn-light border">{{ __('catalog.category.cancel') }}</a>
        </div>
    </form>
</div>
@endsection
