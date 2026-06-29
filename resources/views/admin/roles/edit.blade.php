@include('partials.form_page', [
    'pageTitle' => 'Edit Role',
    'pageEyebrow' => 'Admin / Roles',
    'heading' => 'Edit Role',
    'subtitle' => 'Update role details without affecting current records.',
    'formAction' => route('admin.roles.update', $role),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.roles._form',
])
