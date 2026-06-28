@foreach($formTemplate->fields as $field)
    @php($value = old($field->field_key, $oldValues[$field->field_key] ?? ''))
    <div class="mb-3">
        <label class="form-label">{{ $field->label }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
        @if($field->field_type === 'textarea')
            <textarea name="{{ $field->field_key }}" class="form-control" rows="4" @required($field->is_required)>{{ $value }}</textarea>
        @elseif($field->field_type === 'select')
            <select name="{{ $field->field_key }}" class="form-select" @required($field->is_required)>
                <option value="">-- select --</option>
                @foreach($field->options ?? [] as $option)
                    <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                @endforeach
            </select>
        @elseif($field->field_type === 'file')
            <input type="file" name="{{ $field->field_key }}" class="form-control" @required($field->is_required)>
            <div class="form-text">Max 5MB.</div>
        @else
            <input type="{{ $field->field_type }}" name="{{ $field->field_key }}" class="form-control" value="{{ $value }}" @required($field->is_required)>
        @endif
    </div>
@endforeach
