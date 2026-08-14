@extends('layouts.app')
@section('page_title', __('crm.create_title'))
@section('page_eyebrow', __('crm.eyebrow'))
@section('content')
<x-erp.form-shell
    :title="__('crm.create_title')"
    :eyebrow="__('crm.eyebrow')"
    :description="__('crm.index_description')"
    :action="route('crm.customers.store')"
    :submit-label="__('crm.save')"
    :cancel-url="route('crm.customers.index')"
    :aside-hint="__('crm.form_hint')"
>
    @include('crm.customers._form')
</x-erp.form-shell>
@endsection
