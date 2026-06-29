@include('partials.form_page', [
    'pageTitle' => 'Edit Workflow',
    'pageEyebrow' => 'Admin / Workflows',
    'heading' => 'Edit Workflow',
    'subtitle' => 'Update workflow identity and activation status.',
    'formAction' => route('admin.workflow-templates.update', $workflowTemplate),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.workflow_templates._form',
])
