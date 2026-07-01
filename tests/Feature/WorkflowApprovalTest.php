<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowRequest;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkflowApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $employee;

    private User $otherEmployee;

    private User $manager;

    private User $hr;

    private User $director;

    private FormTemplate $formTemplate;

    private WorkflowTemplate $workflowTemplate;

    private WorkflowStep $managerStep;

    private WorkflowStep $hrStep;

    private WorkflowStep $directorStep;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createWorkflowFixture();
    }

    public function test_employee_can_submit_request_and_manager_is_notified(): void
    {
        $response = $this->actingAs($this->employee)->post(route('employee.requests.store', $this->formTemplate), [
            'reason' => 'Annual leave for family event.',
        ]);

        $response->assertRedirect();

        $workflowRequest = WorkflowRequest::firstOrFail();

        $this->assertSame(WorkflowRequest::STATUS_PENDING, $workflowRequest->status);
        $this->assertSame($this->managerStep->id, $workflowRequest->current_step_id);
        $this->assertDatabaseHas('approval_histories', [
            'request_id' => $workflowRequest->id,
            'workflow_step_id' => $this->managerStep->id,
            'actor_id' => $this->employee->id,
            'action' => 'submit',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->manager->id,
            'type' => Notification::TYPE_REQUEST_SUBMITTED,
        ]);
    }

    public function test_manager_approval_moves_request_to_hr_step(): void
    {
        $workflowRequest = $this->submitRequest();

        $response = $this->actingAs($this->manager)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Looks good.',
        ]);

        $response->assertRedirect(route('manager.approvals.index'));

        $workflowRequest->refresh();

        $this->assertSame(WorkflowRequest::STATUS_PENDING, $workflowRequest->status);
        $this->assertSame($this->hrStep->id, $workflowRequest->current_step_id);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->hr->id,
            'type' => Notification::TYPE_REQUEST_APPROVED,
        ]);
    }

    public function test_hr_approval_moves_request_to_director_step(): void
    {
        $workflowRequest = $this->submitRequest();
        $this->actingAs($this->manager)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Manager approved.',
        ]);

        $response = $this->actingAs($this->hr)->post(route('manager.approvals.approve', $workflowRequest->fresh()), [
            'comment' => 'HR approved.',
        ]);

        $response->assertRedirect(route('manager.approvals.index'));

        $this->assertSame($this->directorStep->id, $workflowRequest->fresh()->current_step_id);
    }

    public function test_director_approval_completes_request_and_notifies_employee(): void
    {
        $workflowRequest = $this->submitRequest();
        $this->actingAs($this->manager)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Manager approved.',
        ]);
        $this->actingAs($this->hr)->post(route('manager.approvals.approve', $workflowRequest->fresh()), [
            'comment' => 'HR approved.',
        ]);

        $response = $this->actingAs($this->director)->post(route('manager.approvals.approve', $workflowRequest->fresh()), [
            'comment' => 'Director approved.',
        ]);

        $response->assertRedirect(route('manager.approvals.index'));

        $workflowRequest->refresh();

        $this->assertSame(WorkflowRequest::STATUS_APPROVED, $workflowRequest->status);
        $this->assertNull($workflowRequest->current_step_id);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee->id,
            'type' => Notification::TYPE_REQUEST_COMPLETED,
        ]);
    }

    public function test_reject_requires_comment(): void
    {
        $workflowRequest = $this->submitRequest();

        $response = $this->actingAs($this->manager)->post(route('manager.approvals.reject', $workflowRequest), [
            'comment' => '',
        ]);

        $response->assertSessionHasErrors('comment');
        $this->assertSame(WorkflowRequest::STATUS_PENDING, $workflowRequest->fresh()->status);
    }

    public function test_return_requires_comment(): void
    {
        $workflowRequest = $this->submitRequest();

        $response = $this->actingAs($this->manager)->post(route('manager.approvals.return', $workflowRequest), [
            'comment' => '',
        ]);

        $response->assertSessionHasErrors('comment');
        $this->assertSame(WorkflowRequest::STATUS_PENDING, $workflowRequest->fresh()->status);
    }

    public function test_return_changes_status_and_notifies_employee(): void
    {
        $workflowRequest = $this->submitRequest();

        $response = $this->actingAs($this->manager)->post(route('manager.approvals.return', $workflowRequest), [
            'comment' => 'Please add more details.',
        ]);

        $response->assertRedirect(route('manager.approvals.index'));

        $this->assertSame(WorkflowRequest::STATUS_RETURNED, $workflowRequest->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->employee->id,
            'type' => Notification::TYPE_REQUEST_RETURNED,
        ]);
    }

    public function test_user_cannot_approve_wrong_workflow_step(): void
    {
        $workflowRequest = $this->submitRequest();

        $response = $this->actingAs($this->hr)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Trying to skip manager.',
        ]);

        $response->assertForbidden();
        $this->assertSame($this->managerStep->id, $workflowRequest->fresh()->current_step_id);
    }

    public function test_completed_request_cannot_be_approved_again(): void
    {
        $workflowRequest = $this->completeRequest();

        $response = $this->actingAs($this->director)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Approve again.',
        ]);

        $response->assertStatus(422);
    }

    public function test_employee_cannot_access_admin_pages(): void
    {
        $response = $this->actingAs($this->employee)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_employee_cannot_view_another_employee_request(): void
    {
        $workflowRequest = $this->submitRequest($this->employee);

        $response = $this->actingAs($this->otherEmployee)->get(route('employee.requests.show', $workflowRequest));

        $response->assertForbidden();
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $workflowRequest = $this->submitRequest();
        $notification = Notification::where('user_id', $this->manager->id)->firstOrFail();

        $response = $this->actingAs($this->employee)->post(route('notifications.read', $notification));

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);

        $this->actingAs($this->manager)->post(route('notifications.read', $notification))->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame($workflowRequest->id, (int) data_get($notification->data, 'request_id'));
    }

    public function test_approval_creates_audit_log_and_approval_history_acted_at(): void
    {
        $workflowRequest = $this->submitRequest();

        $this->actingAs($this->manager)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Approved with audit.',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->manager->id,
            'action' => 'request.approved',
        ]);

        $history = $workflowRequest->histories()->where('action', 'approve')->firstOrFail();

        $this->assertNotNull($history->acted_at);
        $this->assertTrue(AuditLog::where('action', 'request.approved')->exists());
    }

    public function test_laravel_still_works_when_realtime_service_is_unavailable(): void
    {
        config([
            'services.node_notification.url' => 'http://127.0.0.1:1/api/notify',
            'services.node_notification.timeout' => 1,
        ]);

        $response = $this->actingAs($this->employee)->post(route('employee.requests.store', $this->formTemplate), [
            'reason' => 'Realtime service should not block submit.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('requests', [
            'created_by' => $this->employee->id,
            'status' => WorkflowRequest::STATUS_PENDING,
        ]);
    }

    private function submitRequest(?User $employee = null): WorkflowRequest
    {
        $employee ??= $this->employee;

        $this->actingAs($employee)->post(route('employee.requests.store', $this->formTemplate), [
            'reason' => 'Annual leave for family event.',
        ])->assertRedirect();

        return WorkflowRequest::latest('id')->firstOrFail();
    }

    private function completeRequest(): WorkflowRequest
    {
        $workflowRequest = $this->submitRequest();

        $this->actingAs($this->manager)->post(route('manager.approvals.approve', $workflowRequest), [
            'comment' => 'Manager approved.',
        ])->assertRedirect();

        $this->actingAs($this->hr)->post(route('manager.approvals.approve', $workflowRequest->fresh()), [
            'comment' => 'HR approved.',
        ])->assertRedirect();

        $this->actingAs($this->director)->post(route('manager.approvals.approve', $workflowRequest->fresh()), [
            'comment' => 'Director approved.',
        ])->assertRedirect();

        return $workflowRequest->fresh();
    }

    private function createWorkflowFixture(): void
    {
        $adminDepartment = Department::create(['name' => 'Administration', 'code' => 'ADMIN']);
        $engineeringDepartment = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $hrDepartment = Department::create(['name' => 'Human Resources', 'code' => 'HR']);

        $adminRole = Role::create(['name' => 'Admin', 'key' => 'admin']);
        $employeeRole = Role::create(['name' => 'Employee', 'key' => 'employee']);
        $managerRole = Role::create(['name' => 'Manager', 'key' => 'manager']);
        $hrRole = Role::create(['name' => 'HR', 'key' => 'hr']);
        $directorRole = Role::create(['name' => 'Director', 'key' => 'director']);

        $this->admin = $this->createUser('admin@example.com', $adminRole, $adminDepartment);
        $this->employee = $this->createUser('employee@example.com', $employeeRole, $engineeringDepartment);
        $this->otherEmployee = $this->createUser('other@example.com', $employeeRole, $engineeringDepartment);
        $this->manager = $this->createUser('manager@example.com', $managerRole, $engineeringDepartment);
        $this->hr = $this->createUser('hr@example.com', $hrRole, $hrDepartment);
        $this->director = $this->createUser('director@example.com', $directorRole, $adminDepartment);

        $this->formTemplate = FormTemplate::create([
            'name' => 'Leave Request',
            'code' => 'LEAVE',
            'description' => 'Employee leave application form.',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        FormField::create([
            'form_template_id' => $this->formTemplate->id,
            'label' => 'Reason',
            'field_key' => 'reason',
            'field_type' => 'textarea',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->workflowTemplate = WorkflowTemplate::create([
            'form_template_id' => $this->formTemplate->id,
            'name' => 'Leave Approval Flow',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->managerStep = WorkflowStep::create([
            'workflow_template_id' => $this->workflowTemplate->id,
            'step_name' => 'Manager Approval',
            'step_order' => 1,
            'approver_role_id' => $managerRole->id,
        ]);

        $this->hrStep = WorkflowStep::create([
            'workflow_template_id' => $this->workflowTemplate->id,
            'step_name' => 'HR Approval',
            'step_order' => 2,
            'approver_role_id' => $hrRole->id,
        ]);

        $this->directorStep = WorkflowStep::create([
            'workflow_template_id' => $this->workflowTemplate->id,
            'step_name' => 'Director Approval',
            'step_order' => 3,
            'approver_role_id' => $directorRole->id,
        ]);
    }

    private function createUser(string $email, Role $role, Department $department): User
    {
        return User::create([
            'name' => str($email)->before('@')->headline(),
            'email' => $email,
            'password' => Hash::make('password'),
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}
