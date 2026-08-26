<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::where('code', 'ACHIMOTA')->first();

        // Super Admin (platform-level, no school)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@schoolos.com'],
            [
                'uuid'               => Str::uuid(),
                'name'               => 'Super Administrator',
                'first_name'         => 'Super',
                'last_name'          => 'Administrator',
                'email'              => 'superadmin@schoolos.com',
                'password'           => Hash::make('SchoolOS@2024!'),
                'status'             => 'active',
                'is_super_admin'     => true,
                'email_verified_at'  => now(),
            ]
        );
        $superAdmin->assignRole('super-admin');

        if ($school) {
            // School Admin
            $schoolAdmin = User::firstOrCreate(
                ['email' => 'admin@achimota.edu.gh'],
                [
                    'uuid'              => Str::uuid(),
                    'school_id'         => $school->id,
                    'name'              => 'School Administrator',
                    'first_name'        => 'School',
                    'last_name'         => 'Administrator',
                    'email'             => 'admin@achimota.edu.gh',
                    'password'          => Hash::make('Admin@2024!'),
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $schoolAdmin->assignRole('school-admin');

            // Principal
            $principal = User::firstOrCreate(
                ['email' => 'principal@achimota.edu.gh'],
                [
                    'uuid'              => Str::uuid(),
                    'school_id'         => $school->id,
                    'name'              => 'Mrs. Abena Mensah',
                    'first_name'        => 'Abena',
                    'last_name'         => 'Mensah',
                    'email'             => 'principal@achimota.edu.gh',
                    'password'          => Hash::make('Principal@2024!'),
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $principal->assignRole('principal');

            // Demo Teacher
            $teacher = User::firstOrCreate(
                ['email' => 'teacher@achimota.edu.gh'],
                [
                    'uuid'              => Str::uuid(),
                    'school_id'         => $school->id,
                    'name'              => 'Mr. Kwame Asante',
                    'first_name'        => 'Kwame',
                    'last_name'         => 'Asante',
                    'email'             => 'teacher@achimota.edu.gh',
                    'password'          => Hash::make('Teacher@2024!'),
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $teacher->assignRole('teacher');

            // Demo Accountant
            $accountant = User::firstOrCreate(
                ['email' => 'accountant@achimota.edu.gh'],
                [
                    'uuid'              => Str::uuid(),
                    'school_id'         => $school->id,
                    'name'              => 'Ms. Ama Boateng',
                    'first_name'        => 'Ama',
                    'last_name'         => 'Boateng',
                    'email'             => 'accountant@achimota.edu.gh',
                    'password'          => Hash::make('Accountant@2024!'),
                    'status'            => 'active',
                    'email_verified_at' => now(),
                ]
            );
            $accountant->assignRole('accountant');
        }

        $this->command->info('Users seeded. Default credentials:');
        $this->command->info('  Super Admin:  superadmin@schoolos.com / SchoolOS@2024!');
        $this->command->info('  School Admin: admin@achimota.edu.gh / Admin@2024!');
        $this->command->info('  Principal:    principal@achimota.edu.gh / Principal@2024!');
        $this->command->info('  Teacher:      teacher@achimota.edu.gh / Teacher@2024!');
        $this->command->info('  Accountant:   accountant@achimota.edu.gh / Accountant@2024!');
    }
}
