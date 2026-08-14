<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpsertRequest;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private AuditLogService $auditLogService)
    {
    }

    public function index(): View
    {
        $users = User::with(['department', 'role'])->latest()->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'departments' => Department::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(UserUpsertRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);

        $user = User::create($data);
        $this->auditLogService->log('user.created', $user, null, $user->toArray());

        return redirect()->route('admin.users.index')->with('success', __('messages.user_created'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'departments' => Department::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(UserUpsertRequest $request, User $user): RedirectResponse
    {
        $old = $user->toArray();
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $newRole = Role::findOrFail($data['role_id']);
        $removesAdminAccess = $user->hasRole('admin') && (! $data['is_active'] || $newRole->key !== 'admin');

        if ($removesAdminAccess && User::query()->where('is_active', true)->whereHas('role', fn ($query) => $query->where('key', 'admin'))->count() <= 1) {
            return back()->withInput()->with('error', __('messages.last_active_admin_protected'));
        }

        if (! $data['is_active'] && $user->workflowStepsAsApprover()->exists()) {
            return back()->withInput()->with('error', __('messages.user_deactivate_approver_forbidden'));
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $this->auditLogService->log('user.updated', $user, $old, $user->fresh()->toArray());

        return redirect()->route('admin.users.index')->with('success', __('messages.user_updated'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', __('messages.user_self_delete_forbidden'));
        }

        if ($user->hasRole('admin') && $user->is_active
            && User::query()->where('is_active', true)->whereHas('role', fn ($query) => $query->where('key', 'admin'))->count() <= 1) {
            return back()->with('error', __('messages.last_active_admin_protected'));
        }

        if ($user->hasOperationalHistory()) {
            return back()->with('error', __('messages.user_delete_use_deactivate'));
        }

        $old = $user->toArray();
        $this->auditLogService->log('user.deleted', $user, $old, null);
        $user->delete();
        return back()->with('success', __('messages.user_deleted'));
    }
}
