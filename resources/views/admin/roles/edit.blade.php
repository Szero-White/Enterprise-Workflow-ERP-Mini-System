@include('partials.form_page', [
    'pageTitle' => 'Sửa vai trò',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.roles'),
    'heading' => 'Sửa vai trò',
    'subtitle' => 'Cập nhật thông tin vai trò mà không ảnh hưởng dữ liệu hiện có.',
    'formAction' => route('admin.roles.update', $role),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.roles._form',
])
