@extends('layouts.app')

@section('page_title', 'Đăng nhập')

@section('content')
<div class="position-relative overflow-hidden rounded-5" style="background:
    radial-gradient(circle at top left, rgba(59, 130, 246, 0.14), transparent 22%),
    radial-gradient(circle at 85% 15%, rgba(14, 165, 233, 0.12), transparent 18%),
    linear-gradient(135deg, #0f172a 0%, #172554 46%, #1e3a8a 100%);
    box-shadow: 0 28px 70px rgba(15, 23, 42, 0.18);">

    <div class="position-absolute rounded-circle d-none d-xl-block" style="width: 220px; height: 220px; right: -70px; top: -60px; background: rgba(255,255,255,0.06);"></div>
    <div class="position-absolute rounded-circle d-none d-xl-block" style="width: 180px; height: 180px; left: -50px; bottom: -70px; background: rgba(125,211,252,0.10);"></div>

    <div class="row g-0 align-items-stretch position-relative">
        <div class="col-xl-8">
            <div class="p-4 p-xl-5 text-white d-flex flex-column justify-content-between h-100" style="min-height: 540px;">
                <div>
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                        <div class="d-inline-flex align-items-center gap-3">
                            <div class="rounded-4 d-inline-flex align-items-center justify-content-center fw-bold fs-5" style="width: 58px; height: 58px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.14);">
                                EW
                            </div>
                            <div>
                                <div class="text-uppercase small fw-semibold" style="letter-spacing: 0.16em; color: rgba(191, 219, 254, 0.88);">Bản demo portfolio backend</div>
                                <div class="fs-6 fw-semibold">Enterprise Workflow ERP Mini</div>
                            </div>
                        </div>

                        <div class="rounded-pill d-inline-flex align-items-center gap-2 px-3 py-2" style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.12);">
                            <span class="rounded-circle bg-success" style="width: 10px; height: 10px;"></span>
                            <span class="small fw-semibold">Sẵn sàng demo local</span>
                        </div>
                    </div>

                    <div class="row align-items-end g-4">
                        <div class="col-lg-8">
                            <h1 class="fw-bold lh-sm mb-3" style="font-size: clamp(2rem, 3.1vw, 3.6rem); max-width: 14ch;">
                                Quy trình phê duyệt rõ ràng, nhanh và dễ demo.
                            </h1>
                            <p class="mb-0" style="max-width: 40rem; color: rgba(226, 232, 240, 0.84); font-size: 1.05rem;">
                                Một hệ thống ERP mini gọn nhẹ cho biểu mẫu động, phê duyệt nhiều cấp, phân quyền theo vai trò và audit log bằng Laravel, Blade và Bootstrap.
                            </p>
                        </div>
                        <div class="col-lg-4">
                            <div class="rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.12);">
                                <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing: 0.12em; color: rgba(191, 219, 254, 0.88);">Vai trò demo</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.12);">Quản trị</span>
                                    <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.12);">Nhân viên</span>
                                    <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.12);">Quản lý</span>
                                    <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.12);">Nhân sự</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-md-4">
                        <div class="rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.12);">
                            <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing: 0.12em; color: rgba(191, 219, 254, 0.88);">Module</div>
                            <div class="fs-2 fw-bold">06+</div>
                            <div class="small" style="color: rgba(226, 232, 240, 0.76);">Người dùng, biểu mẫu, quy trình, phê duyệt, audit log.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.12);">
                            <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing: 0.12em; color: rgba(191, 219, 254, 0.88);">Luồng</div>
                            <div class="fs-2 fw-bold">3 bước</div>
                            <div class="small" style="color: rgba(226, 232, 240, 0.76);">Chuỗi duyệt Quản lý, Nhân sự và Giám đốc.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="rounded-4 p-3 h-100" style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.12);">
                            <div class="small text-uppercase fw-semibold mb-2" style="letter-spacing: 0.12em; color: rgba(191, 219, 254, 0.88);">Công nghệ</div>
                            <div class="fs-2 fw-bold">Gọn</div>
                            <div class="small" style="color: rgba(226, 232, 240, 0.76);">Laravel, Blade, Bootstrap, MySQL/SQLite.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="h-100 p-3 p-xl-4 d-flex align-items-center justify-content-center">
                <div class="bg-white rounded-5 p-4 w-100" style="max-width: 420px; box-shadow: 0 20px 50px rgba(15, 23, 42, 0.12);">
                    <div class="mb-4">
                        <div class="text-uppercase small fw-semibold text-primary mb-2" style="letter-spacing: 0.14em;">Truy cập bảo mật</div>
                        <h2 class="fw-bold mb-2" style="font-size: 1.7rem;">Đăng nhập dashboard</h2>
                        <p class="text-muted mb-0">Dùng tài khoản demo bên dưới để khám phá luồng phê duyệt.</p>
                    </div>

                    <div class="rounded-4 p-3 mb-4" style="background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid rgba(148, 163, 184, 0.16);">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="small text-uppercase fw-semibold text-muted mb-2">Tài khoản demo</div>
                                <div class="fw-semibold">admin@example.com</div>
                                <div class="text-muted small">password</div>
                            </div>
                            <span class="badge text-bg-primary rounded-pill px-3 py-2">Quản trị</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="email">Email</label>
                            <input id="email" type="email" name="email" class="form-control form-control-lg rounded-4 @error('email') is-invalid @enderror" value="{{ old('email', 'admin@example.com') }}" required autofocus>
                            @include('partials.form_error', ['field' => 'email'])
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="password">{{ __('ui.password') }}</label>
                            <input id="password" type="password" name="password" class="form-control form-control-lg rounded-4 @error('password') is-invalid @enderror" value="password" required>
                            @include('partials.form_error', ['field' => 'password'])
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
                                <label class="form-check-label text-muted" for="remember">Ghi nhớ đăng nhập</label>
                            </div>
                            <span class="small text-muted">Chỉ dùng local</span>
                        </div>

                        <button class="btn btn-primary btn-lg w-100 rounded-4 mb-3" style="box-shadow: 0 14px 30px rgba(37, 99, 235, 0.22);">
                            Đăng nhập bảng điều khiển
                        </button>
                    </form>

                    <div class="pt-3 border-top">
                        <div class="small text-muted mb-2">Điểm nổi bật khi demo:</div>
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Tạo biểu mẫu yêu cầu động</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Duyệt theo vai trò và từng bước quy trình</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span>Theo dõi audit log và lịch sử duyệt</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
