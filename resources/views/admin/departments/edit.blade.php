@include('partials.form_page', [
    'pageTitle' => __('ui.edit_department'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.departments'),
    'heading' => __('ui.edit_department'),
    'subtitle' => __('ui.edit_department_description'),
    'formAction' => route('admin.departments.update', $department),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.departments._form',
])
