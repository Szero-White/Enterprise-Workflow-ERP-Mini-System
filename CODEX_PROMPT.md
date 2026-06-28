# Prompt cho Codex tiếp tục phát triển dự án

Bạn là senior Laravel developer. Hãy phát triển project Laravel Portfolio tên `Enterprise Workflow & ERP Mini System` theo nguyên tắc:

- Không code tất cả một lần.
- Chia nhỏ từng bước.
- Sau mỗi bước phải đảm bảo project chạy được, migration chạy được, route không lỗi, giao diện Blade demo được.
- Không dùng React, Vue, Redis, Elasticsearch, Kubernetes, microservice, CI/CD phức tạp.
- Tập trung Laravel backend, MySQL, Blade, Bootstrap 5, JavaScript/AJAX cơ bản.
- Controller không chứa quá nhiều logic.
- Nghiệp vụ submit/approve/reject/return nằm trong Service Layer và dùng `DB::transaction()`.
- Dùng migration, model, seeder, Eloquent relationship, Form Request validation.

Thứ tự làm:

1. Kiểm tra project Laravel chạy được.
2. Kiểm tra migration + seeder.
3. Kiểm tra login/logout.
4. Kiểm tra phân quyền middleware role.
5. Kiểm tra CRUD role/department/user.
6. Kiểm tra form template + form field builder.
7. Kiểm tra workflow template + workflow step builder.
8. Kiểm tra employee submit dynamic request.
9. Kiểm tra manager approve/reject/return.
10. Kiểm tra audit log, dashboard, filter, pagination.
11. Sau cùng mới thêm Docker Compose nếu project đã ổn.

Khi sửa code, hãy giải thích ngắn:

- Đã sửa file nào.
- Mục đích sửa.
- Lệnh cần chạy.
- Cách test chức năng.
