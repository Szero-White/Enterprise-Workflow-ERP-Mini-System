<div class="row g-3">
    <div class="col-md-6">
        <label for="user_name" class="form-label erp-required">{{ __('ui.name') }}</label>
        <input id="user_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="user_email" class="form-label erp-required">Email</label>
        <input id="user_email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required>
        @include('partials.form_error', ['field' => 'email'])
    </div>
    <div class="col-md-6">
        <label for="user_password" class="form-label {{ isset($user) ? '' : 'erp-required' }}">{{ __('ui.password') }}</label>
        <input id="user_password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ isset($user) ? '' : 'required' }}>
        @if(isset($user))
            <div class="erp-form-hint">{{ __('ui.leave_password_blank') }}</div>
        @endif
        @include('partials.form_error', ['field' => 'password'])
    </div>
    <div class="col-md-6">
        <label for="department_id" class="form-label">{{ __('ui.department') }}</label>
        <select id="department_id" name="department_id" class="form-select @error('department_id') is-invalid @enderror">
            <option value="">{{ __('ui.none') }}</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $user->department_id ?? '') == $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'department_id'])
    </div>
    <div class="col-md-6">
        <label for="role_id" class="form-label erp-required">{{ __('ui.role') }}</label>
        <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected(old('role_id', $user->role_id ?? '') == $role->id)>{{ trans()->has('ui.roles.'.$role->key) ? __('ui.roles.'.$role->key) : $role->name }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'role_id'])
    </div>
    <div class="col-md-6">
        <label class="form-label d-block">{{ __('ui.status') }}</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $user->is_active ?? true))>
            <label class="form-check-label" for="is_active">{{ __('ui.active_user') }}</label>
        </div>
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.users.index')])
