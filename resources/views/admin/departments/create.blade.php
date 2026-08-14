@include('partials.form_page', [
    'pageTitle' => __('ui.create_department'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.departments'),
    'heading' => __('ui.create_department'),
    'subtitle' => __('ui.create_department_description'),
    'formAction' => route('admin.departments.store'),
    'formPartial' => 'admin.departments._form',
])
