<div class="d-flex flex-wrap gap-2 pt-3 mt-3 border-top">
    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2-circle"></i>{{ $submitLabel ?? __('ui.save') }}</button>
    <a href="{{ $cancelUrl }}" class="btn btn-light border px-4">{{ $cancelLabel ?? __('ui.cancel') }}</a>
</div>
