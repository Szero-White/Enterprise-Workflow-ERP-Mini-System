@include('partials.form_page', [
    'pageTitle' => __('ui.create_form_field'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => __('ui.create_form_field_for', ['name' => $formTemplate->name]),
    'subtitle' => __('ui.create_form_field_description'),
    'formAction' => route('admin.form-templates.fields.store', $formTemplate),
    'formPartial' => 'admin.form_fields._form',
])
