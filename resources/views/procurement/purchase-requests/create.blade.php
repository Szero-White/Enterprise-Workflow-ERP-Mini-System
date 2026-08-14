@extends('layouts.app')

@section('page_title', __('procurement.purchase_request.create_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.form-shell
        :title="__('procurement.purchase_request.create_title')"
        :eyebrow="__('procurement.eyebrow')"
        :description="__('procurement.purchase_request.index_description')"
        :action="route('procurement.purchase-requests.store')"
        :submit-label="__('procurement.purchase_request.submit')"
        :cancel-url="route('procurement.purchase-requests.index')"
        aside-hint="Yêu cầu sẽ đi qua workflow phê duyệt trước khi bộ phận mua sắm có thể tạo PO."
    >
        @include('procurement.purchase-requests._form')
    </x-erp.form-shell>
@endsection
