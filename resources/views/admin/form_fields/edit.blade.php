@include('partials.form_page', [
    'pageTitle' => 'Sửa trường',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => 'Sửa trường: '.$formTemplate->name,
    'subtitle' => 'Cập nhật loại trường, key và quy tắc bắt buộc.',
    'formAction' => route('admin.form-templates.fields.update', [$formTemplate, $field]),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.form_fields._form',
])
