<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $departments = Department::latest()->paginate(10);
        return view('admin.departments.index', compact('departments'));
    }

    public function create(): View
    {
        return view('admin.departments.create');
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $department = Department::create($request->validated());
        $this->auditLogService->log('department.created', $department, null, $department->toArray());
        return redirect()->route('admin.departments.index')->with('success', 'Đã tạo phòng ban.');
    }

    public function edit(Department $department): View
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $old = $department->toArray();
        $department->update($request->validated());
        $this->auditLogService->log('department.updated', $department, $old, $department->fresh()->toArray());
        return redirect()->route('admin.departments.index')->with('success', 'Đã cập nhật phòng ban.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $old = $department->toArray();
        $this->auditLogService->log('department.deleted', $department, $old, null);
        $department->delete();
        return back()->with('success', 'Đã xóa phòng ban.');
    }
}
