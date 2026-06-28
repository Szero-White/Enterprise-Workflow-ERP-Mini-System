# Enterprise Workflow & ERP Mini System

Laravel portfolio project cho vị trí Fresher Backend.

Mục tiêu của source pack này là tạo một MVP dễ demo, tập trung vào backend Laravel, MySQL, Blade, Bootstrap và AJAX cơ bản. Không dùng React, Vue, Redis, Elasticsearch, Docker ngay từ đầu.

## 1. Cách cài đặt nhanh

```bash
composer create-project laravel/laravel enterprise-workflow-erp-mini
cd enterprise-workflow-erp-mini
```

Copy toàn bộ nội dung trong thư mục `enterprise-workflow-erp-mini-source` vào project Laravel vừa tạo.

Cấu hình `.env`:

```env
APP_NAME="Enterprise Workflow ERP Mini"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enterprise_workflow_erp
DB_USERNAME=root
DB_PASSWORD=
```

Chạy lệnh:

```bash
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed
php artisan serve
```

Mở:

```text
http://127.0.0.1:8000
```

## 2. Tài khoản demo

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Manager | manager@example.com | password |
| Employee | employee@example.com | password |
| HR | hr@example.com | password |
| Director | director@example.com | password |

## 3. Module đã có trong source pack

- Login / Logout bằng session Laravel Auth.
- Middleware phân quyền theo role.
- CRUD Role.
- CRUD Department.
- CRUD User.
- Dynamic Form Template.
- Dynamic Form Field Builder: text, textarea, number, date, select, file.
- Workflow Template.
- Workflow Step Builder.
- Employee tạo đơn động theo field cấu hình.
- Lưu dữ liệu đơn vào `requests` và `request_values`.
- Lưu file upload vào `attachments`.
- Manager/Admin duyệt đơn: approve, reject, return.
- Tự chuyển step tiếp theo khi approve.
- Audit Log cho thao tác chính.
- Dashboard thống kê.
- Search/filter/pagination cơ bản.

## 4. Ghi chú version Laravel

Source pack này viết theo cấu trúc Laravel 11/12, dùng middleware alias trong `bootstrap/app.php`.

Nếu bạn dùng Laravel 10, hãy đăng ký middleware trong `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ...
    'role' => \App\Http\Middleware\RoleMiddleware::class,
];
```

## 5. Luồng demo nên quay video

1. Login admin.
2. Tạo department, role, user.
3. Tạo mẫu đơn `Leave Request`.
4. Thêm field động: reason, from_date, to_date, leave_type, attachment.
5. Tạo workflow cho mẫu đơn.
6. Thêm step: Manager -> HR -> Director.
7. Login employee.
8. Chọn mẫu đơn và submit đơn.
9. Login manager duyệt bước 1.
10. Login HR duyệt bước 2.
11. Login Director duyệt bước cuối.
12. Kiểm tra trạng thái đơn đã `approved` và xem approval history/audit log.

## 6. Thứ tự triển khai nếu đưa vào Codex

Không yêu cầu Codex code tất cả cùng lúc. Hãy chia theo thứ tự:

1. Chạy migration + seeder trước.
2. Kiểm tra login/logout.
3. Kiểm tra CRUD role/department/user.
4. Kiểm tra tạo form template + form field.
5. Kiểm tra tạo workflow template + workflow step.
6. Kiểm tra employee submit đơn.
7. Kiểm tra manager approve/reject/return.
8. Kiểm tra dashboard, filter, pagination.
9. Sau cùng mới thêm Docker Compose.

## 7. Docker để sau

Khi project đã chạy ổn bằng XAMPP/Laragon/local MySQL, mới thêm Docker. Không cần dùng Docker ngay từ đầu để tránh nặng máy và khó debug.

# Enterprise-Workflow-ERP-Mini-System
