<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $roles = collect([
            ['name' => 'Quản trị viên', 'key' => 'admin'],
            ['name' => 'Quản lý', 'key' => 'manager'],
            ['name' => 'Nhân viên', 'key' => 'employee'],
            ['name' => 'Nhân sự', 'key' => 'hr'],
            ['name' => 'Giám đốc', 'key' => 'director'],
            ['name' => 'Mua sắm', 'key' => 'procurement'],
            ['name' => 'Tài chính', 'key' => 'finance'],
            ['name' => 'Quản lý tài sản', 'key' => 'asset_manager'],
        ])->mapWithKeys(function (array $role) {
            $role['is_system'] = true;

            return [$role['key'] => Role::updateOrCreate(['key' => $role['key']], $role)];
        });

        $departments = collect([
            ['name' => 'Hành chính', 'code' => 'ADMIN'],
            ['name' => 'Nhân sự', 'code' => 'HR'],
            ['name' => 'Kỹ thuật', 'code' => 'ENG'],
            ['name' => 'Kế toán', 'code' => 'ACC'],
            ['name' => 'Mua sắm', 'code' => 'PROC'],
            ['name' => 'Quản lý tài sản', 'code' => 'ASSET'],
        ])->mapWithKeys(fn (array $department) => [
            $department['code'] => Department::updateOrCreate(['code' => $department['code']], $department),
        ]);

        foreach ([
            ['admin@example.com', 'Quản trị hệ thống', 'ADMIN', 'admin'],
            ['manager@example.com', 'Quản lý nhóm', 'ENG', 'manager'],
            ['employee@example.com', 'Nhân viên demo', 'ENG', 'employee'],
            ['hr@example.com', 'Người duyệt nhân sự', 'HR', 'hr'],
            ['director@example.com', 'Người duyệt giám đốc', 'ADMIN', 'director'],
            ['procurement@example.com', 'Chuyên viên mua sắm', 'PROC', 'procurement'],
            ['finance@example.com', 'Chuyên viên tài chính', 'ACC', 'finance'],
            ['asset@example.com', 'Chuyên viên quản lý tài sản', 'ASSET', 'asset_manager'],
        ] as [$email, $name, $departmentCode, $roleKey]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'password' => Hash::make('password'),
                'department_id' => $departments[$departmentCode]->id,
                'role_id' => $roles[$roleKey]->id,
                'is_active' => true,
            ]);
        }
    }
}
