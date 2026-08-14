@extends('layouts.app')
@section('page_title', __('catalog.product.edit_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<div class="content-card erp-form-card p-3 p-lg-4">
    <form method="POST" action="{{ route('catalog.products.update',$product) }}">
        @csrf
        @method('PUT')
        @include('catalog.products._form')
        <div class="d-flex gap-2 mt-4"><button class="btn btn-primary">{{ __('catalog.product.save_changes') }}</button><a href="{{ route('catalog.products.index') }}" class="btn btn-light border">{{ __('catalog.product.back') }}</a></div>
    </form>
</div>
@endsection
