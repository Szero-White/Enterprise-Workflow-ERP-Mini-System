<div class="d-flex flex-wrap gap-2 pt-2">
    <button type="submit" class="btn btn-primary px-4">{{ $submitLabel ?? __('ui.save') }}</button>
    <a href="{{ $cancelUrl }}" class="btn btn-light border px-4">{{ $cancelLabel ?? __('ui.cancel') }}</a>
</div>
