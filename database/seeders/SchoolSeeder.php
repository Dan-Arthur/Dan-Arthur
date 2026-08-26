<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\School;
use App\Models\SystemSetting;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::firstOrCreate(
            ['code' => 'ACHIMOTA'],
            [
                'name'              => 'Achimota School',
                'type'              => 'secondary',
                'motto'             => 'Ut Omnes Unum Sint',
                'address'           => 'Achimota Road, Achimota',
                'city'              => 'Accra',
                'state'             => 'Greater Accra',
                'country'           => 'Ghana',
                'phone'             => '+233-30-222-8560',
                'email'             => 'info@achimota.edu.gh',
                'website'           => 'www.achimotaschool.edu.gh',
                'academic_structure'=> 'term',
                'terms_per_year'    => 3,
                'currency_code'     => 'GHS',
                'currency_symbol'   => '₵',
                'is_active'         => true,
            ]
        );

        $mainCampus = Campus::firstOrCreate(
            ['school_id' => $school->id, 'code' => 'MAIN'],
            [
                'name' => 'Main Campus',
                'is_main_campus' => true,
                'address' => '123 Education Boulevard, Lagos',
                'phone' => '+234-01-234-5678',
                'email' => 'main@greenfieldacademy.edu.ng',
                'is_active' => true,
            ]
        );

        // Academic year
        $academicYear = AcademicYear::firstOrCreate(
            ['school_id' => $school->id, 'name' => '2024/2025'],
            [
                'start_date' => '2024-09-01',
                'end_date' => '2025-07-31',
                'is_current' => true,
                'status' => 'active',
            ]
        );

        // Terms
        $terms = [
            ['name' => 'First Term', 'sequence' => 1, 'start_date' => '2024-09-01', 'end_date' => '2024-12-13', 'is_current' => false, 'status' => 'completed'],
            ['name' => 'Second Term', 'sequence' => 2, 'start_date' => '2025-01-06', 'end_date' => '2025-04-04', 'is_current' => true, 'status' => 'active'],
            ['name' => 'Third Term', 'sequence' => 3, 'start_date' => '2025-04-22', 'end_date' => '2025-07-25', 'is_current' => false, 'status' => 'upcoming'],
        ];

        foreach ($terms as $termData) {
            Term::firstOrCreate(
                ['academic_year_id' => $academicYear->id, 'sequence' => $termData['sequence']],
                $termData
            );
        }

        // System settings
        $settings = [
            ['key' => 'result_approval_required', 'value' => 'true', 'type' => 'boolean', 'group' => 'academic'],
            ['key' => 'show_position_on_result', 'value' => 'true', 'type' => 'boolean', 'group' => 'academic'],
            ['key' => 'attendance_start_time', 'value' => '07:00', 'type' => 'string', 'group' => 'attendance'],
            ['key' => 'late_threshold_minutes', 'value' => '15', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'invoice_due_days', 'value' => '30', 'type' => 'integer', 'group' => 'finance'],
            ['key' => 'student_number_prefix', 'value' => 'GFA', 'type' => 'string', 'group' => 'student'],
            ['key' => 'application_number_prefix', 'value' => 'APP', 'type' => 'string', 'group' => 'admission'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(
                ['school_id' => $school->id, 'key' => $setting['key']],
                array_merge($setting, ['school_id' => $school->id])
            );
        }

        $this->command->info("School '{$school->name}' seeded with academic year and terms.");
    }
}
