<div class="row g-3">
    <div class="col-md-6">
        <label for="step_name" class="form-label erp-required">Tên bước</label>
        <input id="step_name" name="step_name" class="form-control @error('step_name') is-invalid @enderror" value="{{ old('step_name', $step->step_name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'step_name'])
    </div>
    <div class="col-md-6">
        <label for="step_order" class="form-label erp-required">Thứ tự bước</label>
        <input id="step_order" type="number" name="step_order" class="form-control @error('step_order') is-invalid @enderror" value="{{ old('step_order', $step->step_order ?? 1) }}" min="1" required>
        @include('partials.form_error', ['field' => 'step_order'])
    </div>
    <div class="col-md-4">
        <label for="approver_role_id" class="form-label">Vai trò duyệt</label>
        <select id="approver_role_id" name="approver_role_id" class="form-select @error('approver_role_id') is-invalid @enderror">
            <option value="">{{ __('ui.none') }}</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(old('approver_role_id', $step->approver_role_id ?? '') == $role->id)>{{ trans()->has('ui.roles.'.$role->key) ? __('ui.roles.'.$role->key) : $role->name }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'approver_role_id'])
    </div>
    <div class="col-md-4">
        <label for="approver_department_id" class="form-label">Phòng ban duyệt</label>
        <select id="approver_department_id" name="approver_department_id" class="form-select @error('approver_department_id') is-invalid @enderror">
            <option value="">{{ __('ui.none') }}</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('approver_department_id', $step->approver_department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'approver_department_id'])
    </div>
    <div class="col-md-4">
        <label for="approver_user_id" class="form-label">Người duyệt cụ thể</label>
        <select id="approver_user_id" name="approver_user_id" class="form-select @error('approver_user_id') is-invalid @enderror">
            <option value="">{{ __('ui.none') }}</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(old('approver_user_id', $step->approver_user_id ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'approver_user_id'])
    </div>
    <div class="col-12">
        <div class="alert alert-info border-0 rounded-4 mb-0">
            Chọn ít nhất một điều kiện người duyệt. Cách demo đơn giản nhất là chọn vai trò duyệt.
        </div>
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.workflow-templates.show', $workflowTemplate)])
