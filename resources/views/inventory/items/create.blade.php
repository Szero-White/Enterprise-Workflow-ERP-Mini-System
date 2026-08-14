@extends('layouts.app')
@section('page_title', __('items.item.create_title'))
@section('page_eyebrow', __('items.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('items.item.create_title')"
    :eyebrow="__('items.eyebrow')"
    :description="__('items.item.index_description')"
    :action="route('inventory.items.store')"
    :submit-label="__('items.item.save')"
    :cancel-url="route('inventory.items.index')"
    :aside-hint="__('items.item.form_hint')"
>
    @include('inventory.items._form')
</x-erp.form-shell>
@endsection
