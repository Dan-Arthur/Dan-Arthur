<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Schools & Setup
            'view schools', 'create schools', 'edit schools', 'delete schools',
            'view campuses', 'create campuses', 'edit campuses', 'delete campuses',
            'view departments', 'create departments', 'edit departments', 'delete departments',
            'manage settings',

            // Academic Years & Terms
            'view academic years', 'manage academic years',
            'view terms', 'manage terms',

            // Users
            'view users', 'create users', 'edit users', 'delete users', 'manage roles',

            // Students
            'view students', 'create students', 'edit students', 'delete students',
            'view student profiles', 'export students',

            // Admissions
            'view admissions', 'create admissions', 'edit admissions', 'delete admissions',
            'process admissions', 'approve admissions',

            // Guardians
            'view guardians', 'create guardians', 'edit guardians', 'delete guardians',

            // Academic
            'view classes', 'create classes', 'edit classes', 'delete classes',
            'view subjects', 'create subjects', 'edit subjects', 'delete subjects',
            'view timetables', 'manage timetables',

            // Attendance
            'view attendance', 'take attendance', 'edit attendance', 'view attendance reports',

            // Assessments
            'view assessments', 'create assessments', 'edit assessments', 'delete assessments',
            'enter marks', 'view marks',

            // Results
            'view results', 'enter results', 'approve results', 'publish results',
            'view result reports', 'generate report cards',

            // Finance
            'view fees', 'manage fee structures', 'create invoices', 'edit invoices',
            'record payments', 'view payments', 'generate receipts', 'view financial reports',
            'manage scholarships', 'manage discounts',

            // HR
            'view staff', 'create staff', 'edit staff', 'delete staff',
            'view positions', 'manage positions',
            'view leave', 'manage leave', 'approve leave',
            'view staff attendance', 'manage staff attendance',

            // Library
            'view library', 'manage books', 'issue books', 'return books',
            'view library reports',

            // Transport
            'view transport', 'manage vehicles', 'manage routes', 'assign transport',

            // Inventory
            'view inventory', 'manage inventory', 'manage assets',
            'record stock movements',

            // Communication
            'view announcements', 'create announcements', 'manage announcements',
            'send messages', 'view messages',
            'manage events',

            // Discipline
            'view disciplinary records', 'create disciplinary records',
            'edit disciplinary records', 'delete disciplinary records',

            // Analytics & Reports
            'view dashboard', 'view analytics', 'generate reports', 'export reports',

            // Audit
            'view audit logs', 'view activity logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'super-admin' => $permissions, // All permissions
            'school-admin' => array_filter($permissions, fn($p) => !in_array($p, ['view schools', 'create schools', 'edit schools', 'delete schools'])),
            'principal' => [
                'view dashboard', 'view analytics', 'view students', 'view student profiles',
                'view staff', 'view classes', 'view subjects', 'view attendance', 'view attendance reports',
                'view staff attendance',
                'view assessments', 'view marks', 'view results', 'approve results', 'publish results',
                'view result reports', 'generate report cards', 'view fees', 'view payments',
                'view financial reports', 'view library', 'view transport', 'view inventory',
                'view announcements', 'create announcements', 'manage announcements',
                'send messages', 'view messages', 'manage events', 'view disciplinary records',
                'create disciplinary records', 'edit disciplinary records', 'generate reports',
                'export reports', 'view audit logs',
            ],
            'vice-principal' => [
                'view dashboard', 'view students', 'view student profiles', 'view staff',
                'view classes', 'view subjects', 'view attendance', 'take attendance',
                'view attendance reports', 'view assessments', 'view marks', 'view results',
                'view result reports', 'generate report cards', 'view announcements',
                'create announcements', 'send messages', 'view messages', 'manage events',
                'view disciplinary records', 'create disciplinary records',
            ],
            'teacher' => [
                'view dashboard', 'view students', 'view student profiles', 'view classes',
                'view subjects', 'view attendance', 'take attendance', 'view attendance reports',
                'view assessments', 'create assessments', 'edit assessments', 'enter marks',
                'view marks', 'view results', 'view result reports', 'view announcements',
                'send messages', 'view messages', 'view timetables',
            ],
            'accountant' => [
                'view dashboard', 'view students', 'view fees', 'manage fee structures',
                'create invoices', 'edit invoices', 'record payments', 'view payments',
                'generate receipts', 'view financial reports', 'manage scholarships',
                'manage discounts', 'generate reports', 'export reports',
            ],
            'hr-officer' => [
                'view dashboard', 'view staff', 'create staff', 'edit staff',
                'view positions', 'manage positions', 'view leave', 'manage leave',
                'approve leave', 'view staff attendance', 'manage staff attendance',
                'generate reports',
            ],
            'librarian' => [
                'view dashboard', 'view students', 'view library', 'manage books',
                'issue books', 'return books', 'view library reports',
            ],
            'receptionist' => [
                'view dashboard', 'view students', 'view student profiles',
                'view admissions', 'create admissions', 'edit admissions',
                'view guardians', 'create guardians', 'edit guardians',
                'view announcements', 'send messages',
            ],
            'parent' => [
                'view dashboard', 'view students', 'view attendance', 'view results',
                'view fees', 'view payments', 'view announcements', 'send messages',
            ],
            'student' => [
                'view dashboard', 'view attendance', 'view results', 'view announcements',
                'view timetables',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions(array_filter($rolePermissions, fn($p) => in_array($p, $permissions)));
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
