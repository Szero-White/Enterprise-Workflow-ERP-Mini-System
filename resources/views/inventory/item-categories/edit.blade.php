@extends('layouts.app')
@section('page_title', __('items.category.edit_title'))
@section('page_eyebrow', __('items.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('items.category.edit_title')"
    :eyebrow="__('items.eyebrow')"
    :description="$category->code.' · '.$category->name"
    :action="route('inventory.item-categories.update', $category)"
    method="PUT"
    :submit-label="__('items.category.save_changes')"
    :cancel-url="route('inventory.item-categories.index')"
    :cancel-label="__('items.category.back')"
    :aside-hint="__('items.category.form_hint')"
>
    @include('inventory.item-categories._form')
</x-erp.form-shell>
@endsection
