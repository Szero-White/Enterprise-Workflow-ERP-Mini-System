@include('partials.form_page', [
    'pageTitle' => __('ui.create_workflow_step'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => __('ui.create_workflow_step'),
    'subtitle' => __('ui.create_workflow_step_description'),
    'formAction' => route('admin.workflow-templates.steps.store', $workflowTemplate),
    'formPartial' => 'admin.workflow_steps._form',
])
