@extends('layouts.app')
@section('page_title', __('catalog.product.create_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('catalog.product.create_title')"
    :eyebrow="__('catalog.eyebrow')"
    :description="__('catalog.product.index_description')"
    :action="route('catalog.products.store')"
    :submit-label="__('catalog.product.save')"
    :cancel-url="route('catalog.products.index')"
    :aside-hint="__('catalog.product.form_hint')"
>
    @include('catalog.products._form')
</x-erp.form-shell>
@endsection
