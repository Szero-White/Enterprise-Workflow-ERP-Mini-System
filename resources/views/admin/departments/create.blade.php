@include('partials.form_page', [
    'pageTitle' => 'Tạo phòng ban',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.departments'),
    'heading' => 'Tạo phòng ban',
    'subtitle' => 'Thêm phòng ban để tổ chức người dùng và phạm vi phê duyệt.',
    'formAction' => route('admin.departments.store'),
    'formPartial' => 'admin.departments._form',
])
