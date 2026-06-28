<div class="mb-3"><label class="form-label">Step Name</label><input name="step_name" class="form-control" value="{{ old('step_name', $step->step_name ?? '') }}" required></div>
<div class="mb-3"><label class="form-label">Step Order</label><input type="number" name="step_order" class="form-control" value="{{ old('step_order', $step->step_order ?? 1) }}" min="1" required></div>
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Approver Role</label><select name="approver_role_id" class="form-select"><option value="">-- none --</option>@foreach($roles as $role)<option value="{{ $role->id }}" @selected(old('approver_role_id', $step->approver_role_id ?? '') == $role->id)>{{ $role->name }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Approver Department</label><select name="approver_department_id" class="form-select"><option value="">-- none --</option>@foreach($departments as $department)<option value="{{ $department->id }}" @selected(old('approver_department_id', $step->approver_department_id ?? '') == $department->id)>{{ $department->name }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Approver User</label><select name="approver_user_id" class="form-select"><option value="">-- none --</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('approver_user_id', $step->approver_user_id ?? '') == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
</div>
<div class="alert alert-info">Chọn ít nhất 1 điều kiện người duyệt. Demo đơn giản nhất: chọn theo Role.</div>
<button class="btn btn-primary">Save</button><a href="{{ route('admin.workflow-templates.show', $workflowTemplate) }}" class="btn btn-light">Back</a>
