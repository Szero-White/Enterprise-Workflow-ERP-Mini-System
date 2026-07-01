@extends('layouts.app')

@section('page_title', __('menu.create_request'))
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<div class="content-card p-4 p-lg-4 erp-form-card">
    <div class="mb-4">
        <h2 class="erp-section-title">Tạo đơn: {{ $formTemplate->name }}</h2>
        <p class="erp-section-subtitle mb-0">Điền biểu mẫu bên dưới và gửi vào quy trình phê duyệt.</p>
    </div>

    <form method="POST" action="{{ route('employee.requests.store', $formTemplate) }}" enctype="multipart/form-data">
        @csrf
        @include('employee.requests._dynamic_fields', ['formTemplate' => $formTemplate, 'oldValues' => collect()])
        @include('partials.form_actions', ['submitLabel' => __('ui.submit_request'), 'cancelUrl' => route('employee.requests.select-template'), 'cancelLabel' => __('ui.back')])
    </form>
</div>
@endsection
