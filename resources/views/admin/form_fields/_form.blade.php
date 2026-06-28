<div class="mb-3"><label class="form-label">Label</label><input name="label" class="form-control" value="{{ old('label', $field->label ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Field Key</label><input name="field_key" class="form-control" value="{{ old('field_key', $field->field_key ?? '') }}" placeholder="from_date" required><div class="form-text">Chỉ dùng chữ, số, dấu gạch dưới. Ví dụ: leave_reason.</div></div>
<div class="mb-3"><label class="form-label">Field Type</label><select name="field_type" id="field_type" class="form-select" required>@foreach(\App\Models\FormField::TYPES as $type)<option value="{{ $type }}" @selected(old('field_type', $field->field_type ?? '') === $type)>{{ $type }}</option>@endforeach</select></div>
<div class="mb-3" id="options_box"><label class="form-label">Options for select, one line each</label><textarea name="options_text" class="form-control" rows="4">{{ old('options_text', isset($field) && is_array($field->options) ? implode("\n", $field->options) : '') }}</textarea></div>
<div class="mb-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $field->sort_order ?? 0) }}" min="0" required></div>
<div class="form-check mb-3"><input type="hidden" name="is_required" value="0"><input class="form-check-input" type="checkbox" name="is_required" value="1" id="is_required" @checked(old('is_required', $field->is_required ?? false))><label class="form-check-label" for="is_required">Required</label></div>
<button class="btn btn-primary">Save</button><a href="{{ route('admin.form-templates.show', $formTemplate) }}" class="btn btn-light">Back</a>
@push('scripts')
<script>
function toggleOptions(){
    const box = document.getElementById('options_box');
    box.style.display = document.getElementById('field_type').value === 'select' ? 'block' : 'none';
}
document.getElementById('field_type').addEventListener('change', toggleOptions);
toggleOptions();
</script>
@endpush
