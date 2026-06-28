<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\FormField;
use App\Models\FormTemplate;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'Admin', 'key' => 'admin'],
            ['name' => 'Manager', 'key' => 'manager'],
            ['name' => 'Employee', 'key' => 'employee'],
            ['name' => 'HR', 'key' => 'hr'],
            ['name' => 'Director', 'key' => 'director'],
        ])->mapWithKeys(fn ($role) => [$role['key'] => Role::updateOrCreate(['key' => $role['key']], $role)]);

        $departments = collect([
            ['name' => 'Administration', 'code' => 'ADMIN'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Engineering', 'code' => 'ENG'],
            ['name' => 'Accounting', 'code' => 'ACC'],
        ])->mapWithKeys(fn ($department) => [$department['code'] => Department::updateOrCreate(['code' => $department['code']], $department)]);

        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'System Admin',
            'password' => Hash::make('password'),
            'department_id' => $departments['ADMIN']->id,
            'role_id' => $roles['admin']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'manager@example.com'], [
            'name' => 'Team Manager',
            'password' => Hash::make('password'),
            'department_id' => $departments['ENG']->id,
            'role_id' => $roles['manager']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'employee@example.com'], [
            'name' => 'Demo Employee',
            'password' => Hash::make('password'),
            'department_id' => $departments['ENG']->id,
            'role_id' => $roles['employee']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'hr@example.com'], [
            'name' => 'HR Approver',
            'password' => Hash::make('password'),
            'department_id' => $departments['HR']->id,
            'role_id' => $roles['hr']->id,
            'is_active' => true,
        ]);

        User::updateOrCreate(['email' => 'director@example.com'], [
            'name' => 'Director Approver',
            'password' => Hash::make('password'),
            'department_id' => $departments['ADMIN']->id,
            'role_id' => $roles['director']->id,
            'is_active' => true,
        ]);

        $admin = User::where('email', 'admin@example.com')->first();

        $leaveTemplate = FormTemplate::updateOrCreate(['code' => 'LEAVE'], [
            'name' => 'Leave Request',
            'description' => 'Employee leave application form.',
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $fields = [
            ['label' => 'Leave Type', 'field_key' => 'leave_type', 'field_type' => 'select', 'is_required' => true, 'options' => ['Annual Leave', 'Sick Leave', 'Unpaid Leave'], 'sort_order' => 1],
            ['label' => 'From Date', 'field_key' => 'from_date', 'field_type' => 'date', 'is_required' => true, 'options' => null, 'sort_order' => 2],
            ['label' => 'To Date', 'field_key' => 'to_date', 'field_type' => 'date', 'is_required' => true, 'options' => null, 'sort_order' => 3],
            ['label' => 'Reason', 'field_key' => 'reason', 'field_type' => 'textarea', 'is_required' => true, 'options' => null, 'sort_order' => 4],
            ['label' => 'Attachment', 'field_key' => 'attachment', 'field_type' => 'file', 'is_required' => false, 'options' => null, 'sort_order' => 5],
        ];

        foreach ($fields as $field) {
            FormField::updateOrCreate([
                'form_template_id' => $leaveTemplate->id,
                'field_key' => $field['field_key'],
            ], array_merge($field, ['form_template_id' => $leaveTemplate->id]));
        }

        $workflow = WorkflowTemplate::updateOrCreate([
            'form_template_id' => $leaveTemplate->id,
            'name' => 'Leave Approval Flow',
        ], [
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        WorkflowStep::updateOrCreate([
            'workflow_template_id' => $workflow->id,
            'step_order' => 1,
        ], [
            'step_name' => 'Manager Approval',
            'approver_role_id' => $roles['manager']->id,
            'approver_department_id' => null,
            'approver_user_id' => null,
        ]);

        WorkflowStep::updateOrCreate([
            'workflow_template_id' => $workflow->id,
            'step_order' => 2,
        ], [
            'step_name' => 'HR Approval',
            'approver_role_id' => $roles['hr']->id,
            'approver_department_id' => null,
            'approver_user_id' => null,
        ]);

        WorkflowStep::updateOrCreate([
            'workflow_template_id' => $workflow->id,
            'step_order' => 3,
        ], [
            'step_name' => 'Director Approval',
            'approver_role_id' => $roles['director']->id,
            'approver_department_id' => null,
            'approver_user_id' => null,
        ]);
    }
}
