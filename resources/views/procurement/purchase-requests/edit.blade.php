@extends('layouts.app')

@section('page_title', __('procurement.purchase_request.edit_title'))
@section('page_eyebrow', __('procurement.eyebrow'))

@section('content')
    <x-erp.form-shell
        :title="__('procurement.purchase_request.edit_title')"
        :eyebrow="$purchaseRequest->workflowRequest->request_code"
        :description="$purchaseRequest->purpose"
        :action="route('procurement.purchase-requests.update', $purchaseRequest)"
        method="PUT"
        :submit-label="__('procurement.purchase_request.resubmit')"
        :cancel-url="route('procurement.purchase-requests.show', $purchaseRequest)"
        aside-hint="Sau khi gửi lại, yêu cầu tiếp tục từ bước phê duyệt hiện tại."
    >
        @include('procurement.purchase-requests._form', [
            'purchaseRequest' => $purchaseRequest,
            'items' => $items,
        ])
    </x-erp.form-shell>
@endsection
