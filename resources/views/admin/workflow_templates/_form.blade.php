<div class="row g-3">
    <div class="col-md-6">
        <label for="workflow_name" class="form-label erp-required">Name</label>
        <input id="workflow_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $workflowTemplate->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="workflow_form_template_id" class="form-label erp-required">Form Template</label>
        <select id="workflow_form_template_id" name="form_template_id" class="form-select @error('form_template_id') is-invalid @enderror" required>
            @foreach($formTemplates as $template)
                <option value="{{ $template->id }}" @selected(old('form_template_id', $workflowTemplate->form_template_id ?? '') == $template->id)>{{ $template->name }}</option>
            @endforeach
        </select>
        @include('partials.form_error', ['field' => 'form_template_id'])
    </div>
    <div class="col-12">
        <label class="form-label d-block">Status</label>
        <div class="form-check form-switch mt-2">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" @checked(old('is_active', $workflowTemplate->is_active ?? true))>
            <label class="form-check-label" for="is_active">Workflow is active</label>
        </div>
    </div>
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.workflow-templates.index')])
