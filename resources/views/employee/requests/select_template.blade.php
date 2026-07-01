@extends('layouts.app')

@section('page_title', __('menu.create_request'))
@section('page_eyebrow', __('menu.employee').' / '.__('menu.my_requests'))

@section('content')
<div class="mb-3">
    <h2 class="h4 mb-1">Chọn biểu mẫu</h2>
    <p class="text-muted mb-0">Chọn biểu mẫu phù hợp với loại đơn bạn muốn gửi.</p>
</div>

<div class="row g-3">
@forelse($templates as $template)
    <div class="col-md-6 col-xl-4">
        <div class="content-card p-3 p-lg-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h5 class="mb-1">{{ $template->name }}</h5>
                    <div class="text-muted small">{{ $template->code }} &middot; {{ $template->fields_count }} trường</div>
                </div>
                @include('partials.boolean_badge', ['value' => $template->is_active])
            </div>

            <p class="text-muted flex-grow-1 mb-4">{{ $template->description ?: 'Chưa có mô tả.' }}</p>

            <a href="{{ route('employee.requests.create', $template) }}" class="btn btn-primary rounded-3">Tạo đơn</a>
        </div>
    </div>
@empty
    <div class="col-12">
        <div class="content-card p-5 text-center text-muted">Chưa có biểu mẫu đang hoạt động.</div>
    </div>
@endforelse
</div>
@endsection
