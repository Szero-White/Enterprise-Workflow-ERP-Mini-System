@include('partials.form_page', [
    'pageTitle' => 'Tạo vai trò',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.roles'),
    'heading' => 'Tạo vai trò',
    'subtitle' => 'Thêm vai trò mới cho phân quyền và phân công quy trình.',
    'formAction' => route('admin.roles.store'),
    'formPartial' => 'admin.roles._form',
])
