@extends('layouts.app')
@section('page_title', __('items.item.edit_title'))
@section('page_eyebrow', __('items.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('items.item.edit_title')"
    :eyebrow="__('items.eyebrow')"
    :description="$item->sku.' · '.$item->name"
    :action="route('inventory.items.update', $item)"
    method="PUT"
    :submit-label="__('items.item.save_changes')"
    :cancel-url="route('inventory.items.index')"
    :cancel-label="__('items.item.back')"
    :aside-hint="__('items.item.form_hint')"
>
    @include('inventory.items._form')
</x-erp.form-shell>
@endsection
