@extends('layouts.app')
@section('page_title', __('catalog.category.create_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('catalog.category.create_title')"
    :eyebrow="__('catalog.eyebrow')"
    :description="__('catalog.category.index_description')"
    :action="route('catalog.categories.store')"
    :submit-label="__('catalog.category.save')"
    :cancel-url="route('catalog.categories.index')"
    :aside-hint="__('catalog.category.form_hint')"
>
    @include('catalog.categories._form')
</x-erp.form-shell>
@endsection
