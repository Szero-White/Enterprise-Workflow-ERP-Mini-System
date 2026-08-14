@extends('layouts.app')
@section('page_title', __('catalog.category.edit_title'))
@section('page_eyebrow', __('catalog.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('catalog.category.edit_title')"
    :eyebrow="__('catalog.eyebrow')"
    :description="$category->code.' · '.$category->name"
    :action="route('catalog.categories.update', $category)"
    method="PUT"
    :submit-label="__('catalog.category.save_changes')"
    :cancel-url="route('catalog.categories.index')"
    :cancel-label="__('catalog.category.back')"
    :aside-hint="__('catalog.category.form_hint')"
>
    @include('catalog.categories._form')
</x-erp.form-shell>
@endsection
