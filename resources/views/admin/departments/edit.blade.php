@include('partials.form_page', [
    'pageTitle' => 'Sửa phòng ban',
    'pageEyebrow' => __('menu.admin').' / '.__('menu.departments'),
    'heading' => 'Sửa phòng ban',
    'subtitle' => 'Cập nhật thông tin phòng ban trong cơ cấu tổ chức.',
    'formAction' => route('admin.departments.update', $department),
    'formMethod' => 'PUT',
    'formPartial' => 'admin.departments._form',
])
