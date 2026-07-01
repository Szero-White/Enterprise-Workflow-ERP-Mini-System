# Architecture

## Tổng quan

Enterprise Workflow & ERP Mini System dùng Laravel làm core system cho auth, phân quyền, cấu hình biểu mẫu động, workflow approval, audit log và notification. Ứng dụng đang ưu tiên monolith Laravel gọn, dễ chạy local và dễ giải thích khi phỏng vấn.

Kiến trúc chính:

```text
Route -> Controller -> FormRequest -> Service -> Model -> Database
```

## Vì sao dùng Laravel làm core

Laravel phù hợp vì project cần xử lý nghiệp vụ backend nhiều hơn là UI phức tạp. Framework cung cấp sẵn routing, middleware, auth session, validation, migration, Eloquent relationship, transaction và PHPUnit integration.

## Vì sao dùng Blade thay vì React/Vue

Ở giai đoạn portfolio Backend PHP/Laravel, Blade giúp giữ project nhẹ và tập trung vào nghiệp vụ. UI hiện tại chỉ cần form, table, badge, filter, pagination và trang chi tiết request. React/Vue có thể là future improvement nếu cần dashboard tương tác nhiều hơn.

## Dynamic form

Dynamic form cho phép Admin tạo mẫu đơn mà không cần sửa code. `form_templates` lưu thông tin mẫu, `form_fields` lưu field động, còn `request_values` lưu dữ liệu employee nhập theo từng field.

Điểm cần nhớ khi giải thích:

- Form field có type rõ ràng: text, textarea, number, date, select, file.
- Select field cần options hợp lệ.
- Validation request được tạo động từ cấu hình field.

## Workflow template và workflow step

`workflow_templates` mô tả luồng duyệt của một form template. `workflow_steps` mô tả từng bước duyệt và điều kiện người duyệt theo role, department hoặc user cụ thể.

Request đang chờ ở bước nào được xác định bằng `current_step_id` trong bảng `requests`.

## Approval flow

Khi Employee gửi request:

1. Tạo record trong `requests`.
2. Gán workflow active của form.
3. Gán current step đầu tiên.
4. Lưu dynamic values và attachments.
5. Ghi approval history action `submit`.
6. Ghi audit log.
7. Tạo notification cho approver hiện tại.

Khi approve:

1. Kiểm tra request còn `pending`.
2. Kiểm tra user có quyền duyệt current step.
3. Ghi approval history action `approve`.
4. Tìm next step.
5. Nếu còn next step, cập nhật `current_step_id`.
6. Nếu hết step, chuyển request sang `approved`.
7. Ghi audit log.
8. Tạo notification cho approver kế tiếp hoặc creator.

Khi reject/return:

1. Kiểm tra quyền và trạng thái.
2. Bắt buộc comment.
3. Ghi approval history.
4. Cập nhật status `rejected` hoặc `returned`.
5. Ghi audit log.
6. Tạo notification cho Employee.

## Vì sao cần transaction

Approval action thay đổi nhiều bảng cùng lúc: `requests`, `approval_histories`, `audit_logs`, `notifications`. Nếu một bước lỗi, transaction giúp rollback để dữ liệu không bị lệch, ví dụ request đã approved nhưng chưa có approval history.

Các service chính dùng transaction:

- `DynamicRequestService`
- `ApprovalService`

## Approval history

`approval_histories` lưu lịch sử hành động theo request:

- `request_id`
- `workflow_step_id`
- `actor_id`
- `action`
- `comment`
- `acted_at`

Bảng này dùng để giải thích ai đã làm gì, ở bước nào, vào lúc nào.

## Audit log

`audit_logs` ghi thao tác quan trọng của hệ thống, gồm actor, action, description, model bị tác động, old values và new values. Audit log khác approval history ở chỗ audit log dùng rộng cho nhiều module, còn approval history chỉ phục vụ workflow request.

## Notification

Notification được lưu trong Laravel database bằng bảng `notifications`. Cách này đảm bảo thông báo vẫn tồn tại dù user offline hoặc realtime service chưa chạy.

Các loại notification chính:

- `request_submitted`
- `request_approved`
- `request_rejected`
- `request_returned`
- `request_completed`

Laravel có `RealtimeNotificationService` optional để gọi HTTP sang NodeJS sau này. NodeJS không thay thế Laravel và không lưu database.

## NodeJS realtime optional

Project chưa thêm service NodeJS Socket.IO riêng trong repo. Lý do là batch hiện tại ưu tiên backend Laravel bền trước: database notification, authorization, transaction và test. Khi cần realtime demo không reload trang, có thể thêm service NodeJS ở `notification-service/` với endpoint `POST /api/notify` và Socket.IO room theo `user_{id}`.

Nếu NodeJS tắt, Laravel vẫn chạy bình thường vì realtime call được bọc try/catch và timeout ngắn.

## Database chính

- `users`, `roles`, `departments`
- `form_templates`, `form_fields`
- `workflow_templates`, `workflow_steps`
- `requests`, `request_values`, `attachments`
- `approval_histories`
- `audit_logs`
- `notifications`

## Nâng cấp sau

- Docker Compose cho Laravel/MySQL
- REST API version cho mobile/frontend riêng
- NodeJS Socket.IO realtime notification service
- Queue cho notification và audit log nặng
- Policy/Gate chi tiết hơn cho từng resource
- CI chạy `php artisan test`
- Deployment guide
