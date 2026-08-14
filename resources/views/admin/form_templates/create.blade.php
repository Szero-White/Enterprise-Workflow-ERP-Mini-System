@include('partials.form_page', [
    'pageTitle' => __('ui.create_form_template'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.form_templates'),
    'heading' => __('ui.create_form_template'),
    'subtitle' => __('ui.create_form_template_description'),
    'formAction' => route('admin.form-templates.store'),
    'formPartial' => 'admin.form_templates._form',
])
