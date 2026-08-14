@include('partials.form_page', [
    'pageTitle' => __('ui.edit_workflow_step'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => __('ui.edit_workflow_step'),
    'subtitle' => __('ui.edit_workflow_step_description'),
    'formAction' => route('admin.workflow-templates.steps.update', [$workflowTemplate, $step]),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.workflow_steps._form',
])
