@extends('layouts.app')
@section('page_title', __('items.category.create_title'))
@section('page_eyebrow', __('items.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('items.category.create_title')"
    :eyebrow="__('items.eyebrow')"
    :description="__('items.category.index_description')"
    :action="route('inventory.item-categories.store')"
    :submit-label="__('items.category.save')"
    :cancel-url="route('inventory.item-categories.index')"
    :aside-hint="__('items.category.form_hint')"
>
    @include('inventory.item-categories._form')
</x-erp.form-shell>
@endsection
