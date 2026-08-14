@include('partials.form_page', [
    'pageTitle' => __('ui.create_user'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.users'),
    'heading' => __('ui.create_user'),
    'subtitle' => __('ui.create_user_description'),
    'formAction' => route('admin.users.store'),
    'formPartial' => 'admin.users._form',
])
