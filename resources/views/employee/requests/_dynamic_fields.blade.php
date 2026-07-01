@foreach($formTemplate->fields as $field)
    @php($value = old($field->field_key, $oldValues[$field->field_key] ?? ''))
    <div class="mb-3">
        <label class="form-label">{{ $field->label }} @if($field->is_required)<span class="text-danger">*</span>@endif</label>
        @if($field->field_type === 'textarea')
            <textarea name="{{ $field->field_key }}" class="form-control @error($field->field_key) is-invalid @enderror" rows="4" @required($field->is_required)>{{ $value }}</textarea>
        @elseif($field->field_type === 'select')
            <select name="{{ $field->field_key }}" class="form-select @error($field->field_key) is-invalid @enderror" @required($field->is_required)>
                <option value="">{{ __('ui.select_placeholder') }}</option>
                @foreach($field->options ?? [] as $option)
                    <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                @endforeach
            </select>
        @elseif($field->field_type === 'file')
            <input type="file" name="{{ $field->field_key }}" class="form-control @error($field->field_key) is-invalid @enderror" @required($field->is_required)>
            <div class="form-text">{{ __('ui.allowed_file_hint') }}</div>
        @else
            <input type="{{ $field->field_type }}" name="{{ $field->field_key }}" class="form-control @error($field->field_key) is-invalid @enderror" value="{{ $value }}" @required($field->is_required)>
        @endif
        @include('partials.form_error', ['field' => $field->field_key])
    </div>
@endforeach
