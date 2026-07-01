# Enterprise Workflow & ERP Mini System

Dự án portfolio Backend PHP/Laravel mô phỏng một hệ thống nội bộ nhỏ để tạo biểu mẫu động, gửi yêu cầu và duyệt nhiều cấp.

Mục tiêu của project không phải là làm thật nhiều chức năng, mà là thể hiện tư duy backend thực tế: phân quyền rõ, workflow có trạng thái, dữ liệu có quan hệ, thao tác quan trọng có transaction, audit log, notification và test.

## Vì sao không chỉ là CRUD

Project có CRUD cho dữ liệu nền như role, department, user, form template và workflow template. Phần giá trị chính nằm ở luồng nghiệp vụ:

- Admin cấu hình form động và các bước duyệt.
- Employee gửi request theo form đã cấu hình.
- Manager, HR, Director duyệt tuần tự theo workflow step.
- Hệ thống lưu request values, file attachment, approval history, audit log và notification.
- Approval action chạy trong database transaction để tránh trạng thái nửa vời.

## Tech stack

- Laravel 12
- PHP 8.2+
- Blade
- Bootstrap 5
- JavaScript/AJAX cơ bản
- MySQL hoặc SQLite
- PHPUnit
- Vite/Tailwind asset pipeline

## Ngôn ngữ sử dụng trong codebase

- Backend chính: PHP với Laravel.
- Frontend server-rendered: Blade template, HTML, CSS và Bootstrap 5.
- JavaScript: dùng ở mức cơ bản cho tương tác nhỏ trong form động.
- SQL/database: thông qua migration, Eloquent và MySQL hoặc SQLite.
- Node.js: chỉ xuất hiện ở vai trò tooling cho Vite/Tailwind và cấu hình realtime optional, chưa có backend Node.js riêng trong repo.
- Ngôn ngữ giao diện: tiếng Việt qua `lang/vi` và các Blade view đã Việt hóa.

Node.js hiện chỉ dùng cho Vite/Tailwind build asset. Project chưa có backend Node.js Socket.IO riêng trong repo. Laravel đã có `RealtimeNotificationService` dạng optional: nếu cấu hình `NODE_NOTIFICATION_URL`, Laravel sẽ thử gửi realtime notification; nếu service Node tắt, Laravel vẫn chạy bình thường.

## Locale và timezone

Project mặc định dùng:

```env
APP_LOCALE=vi
APP_TIMEZONE=Asia/Ho_Chi_Minh
```

Các trạng thái nghiệp vụ vẫn giữ key nội bộ như `pending`, `approved`, `rejected`, `returned`; phần label hiển thị được dịch sang tiếng Việt.

## Kiến trúc tổng quan

Luồng xử lý chính theo hướng:

```text
Controller -> FormRequest -> Service -> Model
```

- Controller nhận request, gọi service và trả view/redirect.
- FormRequest xử lý validation.
- Service chứa nghiệp vụ như submit request, approval, audit log, notification.
- Model khai báo relationship và metadata trạng thái.

## Module chính

- Login / logout bằng Laravel session auth
- Middleware phân quyền theo role
- CRUD Role, Department, User
- Dynamic Form Template
- Dynamic Form Field Builder: `text`, `textarea`, `number`, `date`, `select`, `file`
- Workflow Template và Workflow Step Builder
- Employee tạo request theo dynamic form
- Lưu dữ liệu request vào `requests` và `request_values`
- File upload lưu vào `attachments`
- Manager/HR/Director duyệt theo từng step
- Approval history với `acted_at`
- Audit log có old/new values và description
- Database notification, mark as read, mark all as read
- Dashboard thống kê
- Search/filter/pagination cơ bản
- PHPUnit feature tests cho workflow, authorization, notification, audit log

## Luồng approval

1. Employee gửi request, trạng thái là `pending`.
2. Request trỏ tới `current_step_id` đầu tiên của workflow.
3. Manager approve thì request chuyển sang step HR.
4. HR approve thì request chuyển sang step Director.
5. Director approve thì request chuyển `approved` và `current_step_id = null`.
6. Reject thì request chuyển `rejected`.
7. Return thì request chuyển `returned` để Employee chỉnh sửa và gửi lại.
8. Mỗi action ghi approval history, audit log và notification.

## Cài đặt local

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

Project hiện chạy local bằng `php artisan serve`, chưa dùng Docker. Docker Compose là hướng nâng cấp sau, không triển khai ở giai đoạn hiện tại.

## Cấu hình database

SQLite mặc định trong `.env.example`:

```env
DB_CONNECTION=sqlite
```

MySQL nếu muốn dùng:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enterprise_workflow_erp
DB_USERNAME=root
DB_PASSWORD=
```

## Notification realtime optional

Laravel luôn lưu notification trong database. Nếu muốn nối thêm NodeJS Socket.IO sau này, cấu hình:

```env
NODE_NOTIFICATION_URL=http://127.0.0.1:3001/api/notify
NODE_NOTIFICATION_TIMEOUT=2
```

Nếu endpoint này không chạy, workflow Laravel vẫn submit/approve bình thường và chỉ ghi warning log.

## Tài khoản demo

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | admin@example.com | password |
| Quản lý | manager@example.com | password |
| Nhân viên | employee@example.com | password |
| Nhân sự | hr@example.com | password |
| Giám đốc | director@example.com | password |

## Luồng demo

1. Đăng nhập bằng tài khoản admin.
2. Tạo phòng ban, vai trò và người dùng nếu cần.
3. Tạo hoặc dùng mẫu đơn `Đơn xin nghỉ phép`.
4. Thêm field động: `reason`, `from_date`, `to_date`, `leave_type`, `attachment`.
5. Tạo workflow cho mẫu đơn.
6. Thêm các step: Quản lý -> Nhân sự -> Giám đốc.
7. Đăng nhập bằng tài khoản nhân viên.
8. Chọn mẫu đơn và gửi request.
9. Manager nhận notification và duyệt bước 1.
10. HR nhận notification và duyệt bước 2.
11. Director nhận notification và duyệt bước cuối.
12. Request chuyển `approved`.
13. Employee nhận notification request đã hoàn tất.
14. Xem approval history và audit log.

## Test

Chạy test:

```bash
php artisan test
```

Nhóm test hiện có kiểm tra:

- Login page và redirect cơ bản
- Employee gửi request thành công
- Employee không vào được admin
- Employee không xem được request của người khác
- Manager/HR/Director duyệt đúng thứ tự
- Không được approve sai step
- Không được approve request đã hoàn tất
- Reject/return bắt buộc comment
- Approval history có `acted_at`
- Audit log được tạo
- Notification được tạo và chỉ đúng user được đọc
- Laravel không lỗi khi realtime service bên ngoài tắt

## Cấu trúc thư mục đáng chú ý

```text
app/Http/Controllers
app/Http/Requests
app/Http/Middleware
app/Models
app/Services
database/migrations
database/seeders
resources/views
tests/Feature
```

## Ghi chú cho nhà tuyển dụng

Project này phù hợp để demo tư duy Backend Laravel ở mức Fresher/Junior: không over-engineering, nhưng có nghiệp vụ nhiều bước, phân quyền, transaction, audit log, notification và feature test đủ để giải thích trong phỏng vấn.

## Future Improvements

- Docker Compose cho Laravel và MySQL
- REST API version
- NodeJS Socket.IO service trong thư mục `notification-service`
- Queue-based notification
- VueJS dashboard components nếu cần UI giàu hơn
- CI/CD pipeline
- Deployment guide
