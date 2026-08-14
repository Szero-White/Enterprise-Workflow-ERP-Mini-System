@include('partials.form_page', [
    'pageTitle' => __('ui.edit_user'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.users'),
    'heading' => __('ui.edit_user'),
    'subtitle' => __('ui.edit_user_description'),
    'formAction' => route('admin.users.update', $user),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.users._form',
])
