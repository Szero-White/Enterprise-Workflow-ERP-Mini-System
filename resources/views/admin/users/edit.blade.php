@include('partials.form_page', [
    'pageTitle' => 'Edit User',
    'pageEyebrow' => 'Admin / Users',
    'heading' => 'Edit User',
    'subtitle' => 'Update profile, access role, and activation status.',
    'formAction' => route('admin.users.update', $user),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.users._form',
])
