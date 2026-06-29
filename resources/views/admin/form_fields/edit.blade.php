@include('partials.form_page', [
    'pageTitle' => 'Edit Field',
    'pageEyebrow' => 'Admin / Form Fields',
    'heading' => 'Edit Field: '.$formTemplate->name,
    'subtitle' => 'Refine field type, key, and validation behavior.',
    'formAction' => route('admin.form-templates.fields.update', [$formTemplate, $field]),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.form_fields._form',
])
