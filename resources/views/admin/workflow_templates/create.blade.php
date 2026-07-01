@include('partials.form_page', [
    'pageTitle' => 'Tạo quy trình duyệt',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.workflow_templates'),
    'heading' => 'Tạo quy trình duyệt',
    'subtitle' => 'Gắn biểu mẫu với một luồng phê duyệt mới.',
    'formAction' => route('admin.workflow-templates.store'),
    'formPartial' => 'admin.workflow_templates._form',
])
