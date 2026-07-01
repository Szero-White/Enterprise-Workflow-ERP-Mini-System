@extends('layouts.app')

@section('page_title', __('menu.dashboard'))
@section('page_eyebrow', 'Tổng quan hệ thống')

@section('content')
<div class="row g-3 mb-4">
    @foreach($stats as $stat)
        <div class="col-md-6 col-xl">
            <div class="content-card h-100 border {{ $stat['card_class'] }}">
                <div class="p-3 p-xl-4 d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="text-uppercase small fw-semibold text-secondary mb-2">{{ $stat['label'] }}</div>
                        <div class="display-6 fw-bold mb-0">{{ $stat['value'] }}</div>
                    </div>
                    <div class="rounded-circle bg-white shadow-sm d-inline-flex align-items-center justify-content-center text-primary" style="width: 52px; height: 52px;">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="content-card p-3 p-lg-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <h2 class="h4 mb-1">Đơn mới nhất</h2>
            <p class="text-muted mb-0">Theo dõi hoạt động gửi đơn gần đây trong toàn bộ quy trình.</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
            <tr>
                <th width="70">{{ __('ui.no') }}</th>
                <th>{{ __('ui.code') }}</th>
                <th>{{ __('ui.form') }}</th>
                <th>{{ __('ui.creator') }}</th>
                <th>{{ __('ui.status') }}</th>
                <th>{{ __('ui.step') }}</th>
                <th>{{ __('ui.created') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($latestRequests as $item)
                <tr>
                    <td class="text-muted fw-semibold">{{ $loop->iteration }}</td>
                    <td class="fw-semibold">{{ $item->request_code }}</td>
                    <td>{{ $item->formTemplate?->name ?? '-' }}</td>
                    <td>{{ $item->creator?->name ?? '-' }}</td>
                    <td>@include('partials.status_badge', ['status' => $item->status])</td>
                    <td>{{ $item->currentStep?->step_name ?? '-' }}</td>
                    <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-muted">Chưa có đơn nào.</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
