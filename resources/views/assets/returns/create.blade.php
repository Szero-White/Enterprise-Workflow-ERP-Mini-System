@extends('layouts.app')

@section('page_title', __('assets.return_form.title'))
@section('page_eyebrow', __('assets.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('assets.return_form.title')"
        :eyebrow="$assignment->asset->asset_code"
        :description="__('assets.return_form.description')"
    />

    <x-erp.panel>
        <form method="POST" action="{{ route('assets.returns.store', $assignment) }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label erp-required">{{ __('assets.return_form.warehouse') }}</label>
                <select name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror" required>
                    <option value="">{{ __('assets.return_form.select_warehouse') }}</option>
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected((string) old('warehouse_id') === (string) $warehouse->id)>
                            {{ $warehouse->code }} - {{ $warehouse->name }}
                        </option>
                    @endforeach
                </select>
                @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label erp-required">{{ __('assets.return_form.returned_at') }}</label>
                <input type="datetime-local" name="returned_at" class="form-control @error('returned_at') is-invalid @enderror" value="{{ old('returned_at', now()->format('Y-m-d\TH:i')) }}" required>
                @error('returned_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label erp-required">{{ __('assets.return_form.condition') }}</label>
                <select name="condition" class="form-select @error('condition') is-invalid @enderror" required>
                    @foreach($conditions as $condition)
                        <option value="{{ $condition->value }}" @selected(old('condition', 'good') === $condition->value)>{{ $condition->label() }}</option>
                    @endforeach
                </select>
                @error('condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('assets.return_form.note') }}</label>
                <textarea name="note" rows="4" class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">{{ __('assets.return_form.submit') }}</button>
                <a class="btn btn-light border" href="{{ route('assets.show', $assignment->asset) }}">{{ __('assets.cancel') }}</a>
            </div>
        </form>
    </x-erp.panel>
@endsection
