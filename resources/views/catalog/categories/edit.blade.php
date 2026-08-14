@extends('layouts.app')
@section('page_title', __('catalog.category.edit_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4">
    <form method="POST" action="{{ route('catalog.categories.update', $category) }}">
        @csrf
        @method('PUT')
        @include('catalog.categories._form')
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary">{{ __('catalog.category.save_changes') }}</button>
            <a href="{{ route('catalog.categories.index') }}" class="btn btn-light border">{{ __('catalog.category.back') }}</a>
        </div>
    </form>
</div>
@endsection
