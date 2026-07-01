@include('partials.form_page', [
    'pageTitle' => 'Sửa biểu mẫu',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => 'Sửa biểu mẫu',
    'subtitle' => 'Cập nhật thông tin biểu mẫu trước khi quản lý các trường.',
    'formAction' => route('admin.form-templates.update', $formTemplate),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.form_templates._form',
])
