@include('partials.form_page', [
    'pageTitle' => __('ui.edit_workflow_template'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => __('ui.edit_workflow_template'),
    'subtitle' => __('ui.edit_workflow_template_description'),
    'formAction' => route('admin.workflow-templates.update', $workflowTemplate),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.workflow_templates._form',
])
