@include('partials.form_page', [
    'pageTitle' => 'Tạo người dùng',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.users'),
    'heading' => 'Tạo người dùng',
    'subtitle' => 'Tạo tài khoản mới với vai trò và phòng ban phù hợp.',
    'formAction' => route('admin.users.store'),
    'formPartial' => 'admin.users._form',
])
