@include('partials.form_page', [
    'pageTitle' => 'Edit Form Template',
    'pageEyebrow' => 'Admin / Form Templates',
    'heading' => 'Edit Form Template',
    'subtitle' => 'Adjust the template definition before managing its fields.',
    'formAction' => route('admin.form-templates.update', $formTemplate),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.form_templates._form',
])
