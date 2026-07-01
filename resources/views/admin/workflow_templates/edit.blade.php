@include('partials.form_page', [
    'pageTitle' => 'Sửa quy trình duyệt',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => 'Sửa quy trình duyệt',
    'subtitle' => 'Cập nhật thông tin quy trình và trạng thái hoạt động.',
    'formAction' => route('admin.workflow-templates.update', $workflowTemplate),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.workflow_templates._form',
])
