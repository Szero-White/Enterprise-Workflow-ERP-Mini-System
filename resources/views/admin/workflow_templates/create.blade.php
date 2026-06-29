@include('partials.form_page', [
    'pageTitle' => 'Create Workflow',
    'pageEyebrow' => 'Admin / Workflows',
    'heading' => 'Create Workflow',
    'subtitle' => 'Assign a form template to a new approval flow.',
    'formAction' => route('admin.workflow-templates.store'),
    'formPartial' => 'admin.workflow_templates._form',
])
