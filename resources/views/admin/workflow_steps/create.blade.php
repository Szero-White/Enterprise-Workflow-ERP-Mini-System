@include('partials.form_page', [
    'pageTitle' => 'Thêm bước duyệt',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => 'Thêm bước duyệt',
    'subtitle' => 'Định nghĩa bước phê duyệt tiếp theo cho quy trình này.',
    'formAction' => route('admin.workflow-templates.steps.store', $workflowTemplate),
    'formPartial' => 'admin.workflow_steps._form',
])
