@extends('layouts.app')

@section('page_title', __('assets.edit_title'))
@section('page_eyebrow', __('assets.eyebrow'))

@section('content')
    <x-erp.page-header
        :title="__('assets.edit_title')"
        :eyebrow="$asset->asset_code"
        :description="$asset->item->sku.' · '.$asset->item->name"
    />

    <x-erp.panel>
        <form method="POST" action="{{ route('assets.update', $asset) }}" class="row g-3">
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label class="form-label">{{ __('assets.serial_number') }}</label>
                <input name="serial_number" class="form-control @error('serial_number') is-invalid @enderror" value="{{ old('serial_number', $asset->serial_number) }}">
                @error('serial_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12">
                <label class="form-label">{{ __('assets.note') }}</label>
                <textarea name="note" rows="4" class="form-control @error('note') is-invalid @enderror">{{ old('note', $asset->note) }}</textarea>
                @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-primary">{{ __('assets.save') }}</button>
                <a class="btn btn-light border" href="{{ route('assets.show', $asset) }}">{{ __('assets.cancel') }}</a>
            </div>
        </form>
    </x-erp.panel>
@endsection
