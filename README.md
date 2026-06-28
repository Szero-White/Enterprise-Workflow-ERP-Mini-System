# Enterprise Workflow & ERP Mini System

Laravel portfolio project cho vị trí Fresher Backend.

Project này hiện đã là một Laravel 12 app hoàn chỉnh, không còn ở dạng "source pack" rời nữa. Mục tiêu là tạo một MVP dễ demo, tập trung vào backend Laravel, MySQL hoặc SQLite, Blade, Bootstrap và AJAX cơ bản.

## Cài đặt nhanh

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate:fresh --seed
php artisan serve
```

Mở:

```text
http://127.0.0.1:8000
```

## Cấu hình database

Bạn có thể dùng MySQL bằng cách chỉnh `.env`:

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

Hoặc dùng SQLite cho local dev nếu muốn nhẹ hơn.

## Tài khoản demo

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Manager | manager@example.com | password |
| Employee | employee@example.com | password |
| HR | hr@example.com | password |
| Director | director@example.com | password |

## Module hiện có

- Login / Logout bằng session Laravel Auth
- Middleware phân quyền theo role
- CRUD Role
- CRUD Department
- CRUD User
- Dynamic Form Template
- Dynamic Form Field Builder: text, textarea, number, date, select, file
- Workflow Template
- Workflow Step Builder
- Employee tạo đơn động theo field cấu hình
- Lưu dữ liệu đơn vào `requests` và `request_values`
- Lưu file upload vào `attachments`
- Manager/Admin duyệt đơn: approve, reject, return
- Tự chuyển step tiếp theo khi approve
- Audit Log cho thao tác chính
- Dashboard thống kê
- Search / filter / pagination cơ bản

## Version note

Code được ghép theo cấu trúc Laravel 11/12 và hiện đang chạy trên Laravel 12.

## Demo flow

1. Login admin
2. Tạo department, role, user
3. Tạo mẫu đơn `Leave Request`
4. Thêm field động: `reason`, `from_date`, `to_date`, `leave_type`, `attachment`
5. Tạo workflow cho mẫu đơn
6. Thêm step: Manager -> HR -> Director
7. Login employee
8. Chọn mẫu đơn và submit đơn
9. Login manager duyệt bước 1
10. Login HR duyệt bước 2
11. Login Director duyệt bước cuối
12. Kiểm tra trạng thái đơn đã `approved` và xem approval history / audit log
