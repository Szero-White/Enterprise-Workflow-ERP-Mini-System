@include('partials.form_page', [
    'pageTitle' => 'Create Role',
    'pageEyebrow' => 'Admin / Roles',
    'heading' => 'Create Role',
    'subtitle' => 'Add a new role for access control and workflow assignment.',
    'formAction' => route('admin.roles.store'),
    'formPartial' => 'admin.roles._form',
])
