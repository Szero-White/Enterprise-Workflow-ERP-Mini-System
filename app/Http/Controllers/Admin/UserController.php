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
        $old = $user->toArray();
        $this->auditLogService->log('user.deleted', $user, $old, null);
        $user->delete();
        return back()->with('success', __('messages.user_deleted'));
    }
}
