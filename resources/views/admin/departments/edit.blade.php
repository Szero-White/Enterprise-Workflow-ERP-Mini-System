@include('partials.form_page', [
    'pageTitle' => 'Edit Department',
    'pageEyebrow' => 'Admin / Departments',
    'heading' => 'Edit Department',
    'subtitle' => 'Update department information for organization structure.',
    'formAction' => route('admin.departments.update', $department),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.departments._form',
])
