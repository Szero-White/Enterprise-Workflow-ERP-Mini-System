@include('partials.form_page', [
    'pageTitle' => 'Sửa bước duyệt',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => 'Sửa bước duyệt',
    'subtitle' => 'Cập nhật thứ tự và điều kiện người duyệt của bước này.',
    'formAction' => route('admin.workflow-templates.steps.update', [$workflowTemplate, $step]),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.workflow_steps._form',
])
