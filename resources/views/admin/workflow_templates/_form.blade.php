<div class="row g-3">
    @isset($workflowTemplate)
        <div class="col-12">
            <div class="erp-inline-note">
                <i class="bi bi-bezier2"></i>
                <span>{{ __('ui.version') }} <strong>v{{ $workflowTemplate->version }}</strong>. {{ $workflowTemplate->is_active ? __('ui.configuration_active_hint') : __('ui.workflow_template_draft_hint') }}</span>
            </div>
        </div>
    @endisset

    <div class="col-md-6">
        <label for="workflow_name" class="form-label erp-required">{{ __('ui.name') }}</label>
        <input id="workflow_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $workflowTemplate->name ?? '') }}" required>
        @include('partials.form_error', ['field' => 'name'])
    </div>
    <div class="col-md-6">
        <label for="workflow_form_template_id" class="form-label erp-required">{{ __('ui.form') }}</label>
        @isset($workflowTemplate)
            <input type="hidden" name="form_template_id" value="{{ $workflowTemplate->form_template_id }}">
            <input id="workflow_form_template_id" class="form-control" value="{{ $workflowTemplate->formTemplate?->displayName() ?? '-' }}" disabled>
        @else
            <select id="workflow_form_template_id" name="form_template_id" class="form-select @error('form_template_id') is-invalid @enderror" required>
                @foreach($formTemplates as $template)
                    <option value="{{ $template->id }}" @selected(old('form_template_id') == $template->id)>{{ $template->displayName() }}</option>
                @endforeach
            </select>
        @endisset
        @include('partials.form_error', ['field' => 'form_template_id'])
    </div>

    @unless(isset($workflowTemplate))
        <div class="col-12">
            <div class="erp-inline-note">
                <i class="bi bi-info-circle"></i>
                <span>{{ __('ui.workflow_template_draft_hint') }}</span>
            </div>
        </div>
    @endunless
</div>

@include('partials.form_actions', ['cancelUrl' => route('admin.workflow-templates.index')])
