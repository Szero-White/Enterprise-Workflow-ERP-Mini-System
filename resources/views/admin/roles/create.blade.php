@include('partials.form_page', [
    'pageTitle' => __('ui.create_role'),
    'pageEyebrow' => __('menu.admin').' / '.__('menu.roles'),
    'heading' => __('ui.create_role'),
    'subtitle' => __('ui.create_role_description'),
    'formAction' => route('admin.roles.store'),
    'formPartial' => 'admin.roles._form',
])
