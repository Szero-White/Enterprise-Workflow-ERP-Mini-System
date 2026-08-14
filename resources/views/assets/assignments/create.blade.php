@extends('layouts.app')

@section('page_title', __('assets.assignment.title'))
@section('page_eyebrow', __('assets.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('assets.assignment.title')"
        :eyebrow="$asset->asset_code"
        :description="__('assets.assignment.description')"
    />

    <div class="row g-3">
        <div class="col-lg-8">
            <x-erp.panel>
                <form method="POST" action="{{ route('assets.assignments.store', $asset) }}" class="row g-3">
                    @csrf
                    <div class="col-md-7">
                        <label class="form-label erp-required">{{ __('assets.assignment.assignee') }}</label>
                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                            <option value="">{{ __('assets.assignment.select_assignee') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('assigned_to') === (string) $user->id)>
                                    {{ $user->name }} · {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label erp-required">{{ __('assets.assignment.assigned_at') }}</label>
                        <input type="datetime-local" name="assigned_at" class="form-control @error('assigned_at') is-invalid @enderror" value="{{ old('assigned_at', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('assigned_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">{{ __('assets.assignment.expected_return_at') }}</label>
                        <input type="date" name="expected_return_at" class="form-control @error('expected_return_at') is-invalid @enderror" value="{{ old('expected_return_at') }}">
                        @error('expected_return_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('assets.assignment.purpose') }}</label>
                        <textarea name="purpose" rows="4" class="form-control @error('purpose') is-invalid @enderror">{{ old('purpose') }}</textarea>
                        @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-primary">{{ __('assets.assignment.submit') }}</button>
                        <a class="btn btn-light border" href="{{ route('assets.show', $asset) }}">{{ __('assets.cancel') }}</a>
                    </div>
                </form>
            </x-erp.panel>
        </div>
        <div class="col-lg-4">
            <x-erp.panel :title="$asset->asset_code">
                <div class="erp-record-primary">{{ $asset->item->name }}</div>
                <div class="erp-record-secondary">{{ $asset->item->sku }}</div>
                <hr>
                <div class="small text-muted">{{ __('assets.warehouse') }}</div>
                <div>{{ $asset->warehouse->name }}</div>
            </x-erp.panel>
        </div>
    </div>
@endsection
