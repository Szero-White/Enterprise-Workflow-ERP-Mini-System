@include('partials.form_page', [
    'pageTitle' => __('ui.edit_form_template'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => __('ui.edit_form_template'),
    'subtitle' => __('ui.edit_form_template_description'),
    'formAction' => route('admin.form-templates.update', $formTemplate),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.form_templates._form',
])
