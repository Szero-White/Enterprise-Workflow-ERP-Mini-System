@include('partials.form_page', [
    'pageTitle' => 'Tạo biểu mẫu',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => 'Tạo biểu mẫu',
    'subtitle' => 'Tạo biểu mẫu yêu cầu có thể tái sử dụng cho nhân viên.',
    'formAction' => route('admin.form-templates.store'),
    'formPartial' => 'admin.form_templates._form',
])
