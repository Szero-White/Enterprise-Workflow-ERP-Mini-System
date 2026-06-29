@include('partials.form_page', [
    'pageTitle' => 'Add Field',
    'pageEyebrow' => 'Admin / Form Fields',
    'heading' => 'Add Field: '.$formTemplate->name,
    'subtitle' => 'Configure a new dynamic field for this form template.',
    'formAction' => route('admin.form-templates.fields.store', $formTemplate),
    'formPartial' => 'admin.form_fields._form',
])
