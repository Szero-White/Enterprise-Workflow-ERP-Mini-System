<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $roles = Role::latest()->paginate(10);
        return view('admin.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('admin.roles.create');
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->validated());
        $this->auditLogService->log('role.created', $role, null, $role->toArray());
        return redirect()->route('admin.roles.index')->with('success', __('messages.role_created'));
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $old = $role->toArray();
        $role->update($request->validated());
        $this->auditLogService->log('role.updated', $role, $old, $role->fresh()->toArray());
        return redirect()->route('admin.roles.index')->with('success', __('messages.role_updated'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $old = $role->toArray();
        $this->auditLogService->log('role.deleted', $role, $old, null);
        $role->delete();
        return back()->with('success', __('messages.role_deleted'));
    }
}
