@include('partials.form_page', [
    'pageTitle' => 'Create Department',
    'pageEyebrow' => 'Admin / Departments',
    'heading' => 'Create Department',
    'subtitle' => 'Add a department to organize users and approval scopes.',
    'formAction' => route('admin.departments.store'),
    'formPartial' => 'admin.departments._form',
])
