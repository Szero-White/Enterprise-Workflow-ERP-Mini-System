@extends('layouts.app')

@section('page_title', 'Sửa đơn bị trả về')
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<div class="content-card p-4 p-lg-4 erp-form-card">
    <div class="mb-4">
        <h2 class="erp-section-title">Sửa đơn bị trả về: {{ $workflowRequest->request_code }}</h2>
        <p class="erp-section-subtitle mb-0">Cập nhật các thông tin cần thiết và gửi lại đơn để phê duyệt.</p>
    </div>

    <form method="POST" action="{{ route('employee.requests.update', $workflowRequest) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => $oldValues])
        @include('partials.form_actions', ['submitLabel' => __('ui.resubmit'), 'cancelUrl' => route('employee.requests.show', $workflowRequest), 'cancelLabel' => __('ui.back')])
    </form>
</div>
@endsection
