<?php

namespace Tests\Feature\Workflow;

use App\Models\Department;
use App\Models\FormTemplate;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RequestNotificationFilterExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_my_requests_keyword_matches_request_code_or_form_name(): void
    {
        $employee = $this->createEmployee();
        $overtimeForm = $this->createForm($employee, 'Đề nghị làm thêm giờ', 'OVERTIME');
        $leaveForm = $this->createForm($employee, 'Đơn xin nghỉ phép', 'LEAVE');

        $overtimeRequest = $this->createRequest($employee, $overtimeForm, 'OVERTIME-001');
        $leaveRequest = $this->createRequest($employee, $leaveForm, 'LEAVE-001');

        $this->actingAs($employee)
            ->get(route('employee.requests.index', ['keyword' => 'làm thêm giờ']))
            ->assertOk()
            ->assertSee(__('filters.request_code_or_form'))
            ->assertSee($overtimeRequest->request_code)
            ->assertDontSee($leaveRequest->request_code);

        $this->actingAs($employee)
            ->get(route('employee.requests.index', ['keyword' => 'LEAVE-001']))
            ->assertOk()
            ->assertSee($leaveRequest->request_code)
            ->assertDontSee($overtimeRequest->request_code);
    }

    public function test_notification_center_can_show_only_unread_and_marked_unread_items_reappear(): void
    {
        $employee = $this->createEmployee();

        $unread = Notification::create([
            'user_id' => $employee->id,
            'title' => 'Thông báo chưa đọc',
            'message' => 'Nội dung chưa đọc.',
            'type' => Notification::TYPE_REQUEST_SUBMITTED,
        ]);

        $read = Notification::create([
            'user_id' => $employee->id,
            'title' => 'Thông báo đã đọc',
            'message' => 'Nội dung đã đọc.',
            'type' => Notification::TYPE_REQUEST_COMPLETED,
            'read_at' => now(),
        ]);

        $this->actingAs($employee)
            ->get(route('notifications.index', ['view' => 'unread']))
            ->assertOk()
            ->assertSee(__('filters.all_notifications'))
            ->assertSee($unread->title)
            ->assertDontSee($read->title);

        $this->actingAs($employee)
            ->post(route('notifications.unread', $read))
            ->assertRedirect();

        $this->assertNull($read->fresh()->read_at);

        $this->actingAs($employee)
            ->get(route('notifications.index', ['view' => 'unread']))
            ->assertOk()
            ->assertSee($unread->title)
            ->assertSee($read->title);
    }

    private function createEmployee(): User
    {
        $department = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);
        $role = Role::create([
            'name' => 'Employee',
            'key' => 'employee',
        ]);

        return User::create([
            'name' => 'Employee Tester',
            'email' => 'employee-filter@example.com',
            'password' => Hash::make('password'),
            'department_id' => $department->id,
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }

    private function createForm(User $employee, string $name, string $code): FormTemplate
    {
        return FormTemplate::create([
            'name' => $name,
            'code' => $code,
            'description' => $name,
            'is_active' => true,
            'created_by' => $employee->id,
        ]);
    }

    private function createRequest(User $employee, FormTemplate $formTemplate, string $requestCode): WorkflowRequest
    {
        return WorkflowRequest::create([
            'request_code' => $requestCode,
            'form_template_id' => $formTemplate->id,
            'created_by' => $employee->id,
            'status' => WorkflowRequest::STATUS_PENDING,
            'submitted_at' => now(),
        ]);
    }
}
