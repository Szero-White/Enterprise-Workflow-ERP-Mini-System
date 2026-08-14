@include('partials.form_page', [
    'pageTitle' => __('ui.create_workflow_template'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => __('ui.create_workflow_template'),
    'subtitle' => __('ui.create_workflow_template_description'),
    'formAction' => route('admin.workflow-templates.store'),
    'formPartial' => 'admin.workflow_templates._form',
])
