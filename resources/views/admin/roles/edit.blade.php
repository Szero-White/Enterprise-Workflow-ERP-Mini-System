@include('partials.form_page', [
    'pageTitle' => __('ui.edit_role'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.roles'),
    'heading' => __('ui.edit_role'),
    'subtitle' => __('ui.edit_role_description'),
    'formAction' => route('admin.roles.update', $role),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.roles._form',
])
