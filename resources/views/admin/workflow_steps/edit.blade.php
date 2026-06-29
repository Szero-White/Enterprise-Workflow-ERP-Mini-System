@include('partials.form_page', [
    'pageTitle' => 'Edit Workflow Step',
    'pageEyebrow' => 'Admin / Workflow Steps',
    'heading' => 'Edit Workflow Step',
    'subtitle' => 'Adjust order and approver rules for this step.',
    'formAction' => route('admin.workflow-templates.steps.update', [$workflowTemplate, $step]),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.workflow_steps._form',
])
