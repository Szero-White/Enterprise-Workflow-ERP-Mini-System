@include('partials.form_page', [
    'pageTitle' => 'Create Form Template',
    'pageEyebrow' => 'Admin / Form Templates',
    'heading' => 'Create Form Template',
    'subtitle' => 'Create a reusable request form for employees.',
    'formAction' => route('admin.form-templates.store'),
    'formPartial' => 'admin.form_templates._form',
])
