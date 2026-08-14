@extends('layouts.app')
@section('page_title', __('catalog.product.edit_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('catalog.product.edit_title')"
    :eyebrow="__('catalog.eyebrow')"
    :description="$product->sku.' · '.$product->name"
    :action="route('catalog.products.update', $product)"
    method="PUT"
    :submit-label="__('catalog.product.save_changes')"
    :cancel-url="route('catalog.products.index')"
    :cancel-label="__('catalog.product.back')"
    :aside-hint="__('catalog.product.form_hint')"
>
    @include('catalog.products._form')
</x-erp.form-shell>
@endsection
