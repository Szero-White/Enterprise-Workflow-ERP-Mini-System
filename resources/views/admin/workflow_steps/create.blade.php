@include('partials.form_page', [
    'pageTitle' => 'Add Workflow Step',
    'pageEyebrow' => 'Admin / Workflow Steps',
    'heading' => 'Add Workflow Step',
    'subtitle' => 'Define the next approval stage for this workflow.',
    'formAction' => route('admin.workflow-templates.steps.store', $workflowTemplate),
    'formPartial' => 'admin.workflow_steps._form',
])
