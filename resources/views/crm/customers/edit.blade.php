@extends('layouts.app')
@section('page_title', __('crm.edit_title'))
@section('page_eyebrow', __('crm.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('crm.edit_title')"
    :eyebrow="__('crm.eyebrow')"
    :description="$customer->code.' · '.$customer->name"
    :action="route('crm.customers.update', $customer)"
    method="PUT"
    :submit-label="__('crm.save_changes')"
    :cancel-url="route('crm.customers.index')"
    :cancel-label="__('crm.back')"
    :aside-hint="__('crm.form_hint')"
>
    @include('crm.customers._form')
</x-erp.form-shell>
@endsection
