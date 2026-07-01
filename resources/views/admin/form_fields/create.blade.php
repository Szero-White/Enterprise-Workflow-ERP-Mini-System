@include('partials.form_page', [
    'pageTitle' => 'Thêm trường',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => 'Thêm trường: '.$formTemplate->name,
    'subtitle' => 'Cấu hình trường động mới cho biểu mẫu này.',
    'formAction' => route('admin.form-templates.fields.store', $formTemplate),
    'formPartial' => 'admin.form_fields._form',
])
