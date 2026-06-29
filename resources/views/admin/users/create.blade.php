@include('partials.form_page', [
    'pageTitle' => 'Create User',
    'pageEyebrow' => 'Admin / Users',
    'heading' => 'Create User',
    'subtitle' => 'Set up a new account with the correct role and department.',
    'formAction' => route('admin.users.store'),
    'formPartial' => 'admin.users._form',
])
