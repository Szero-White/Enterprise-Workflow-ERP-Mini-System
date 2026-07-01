@include('partials.form_page', [
    'pageTitle' => 'Sửa người dùng',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.users'),
    'heading' => 'Sửa người dùng',
    'subtitle' => 'Cập nhật hồ sơ, vai trò truy cập và trạng thái hoạt động.',
    'formAction' => route('admin.users.update', $user),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.users._form',
])
