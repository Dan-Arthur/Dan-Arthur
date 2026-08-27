<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class GhanaianCurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        if (!$school) {
            $this->command->warn('No school found. Skipping curriculum seed.');
            return;
        }

        $schoolId = $school->id;

        // ================================================================
        // SHS DEPARTMENTS
        // ================================================================
        $deptDefs = [
            ['code' => 'SCI-DEPT',  'name' => 'Science Department',           'description' => 'General Science Programme'],
            ['code' => 'HUM-DEPT',  'name' => 'Humanities Department',         'description' => 'General Arts / Humanities Programme'],
            ['code' => 'BUS-DEPT',  'name' => 'Business Department',           'description' => 'Business Programme'],
            ['code' => 'AGRI-DEPT', 'name' => 'Agricultural Science Dept.',    'description' => 'Agricultural Science Programme'],
            ['code' => 'HEC-DEPT',  'name' => 'Home Economics Department',     'description' => 'Home Economics Programme'],
            ['code' => 'VART-DEPT', 'name' => 'Visual Arts Department',        'description' => 'Visual Arts Programme'],
            ['code' => 'PART-DEPT', 'name' => 'Performing Arts Department',    'description' => 'Performing Arts Programme'],
            ['code' => 'TECH-DEPT', 'name' => 'Technical / Engineering Dept.', 'description' => 'Technical / Vocational Engineering Programme'],
            ['code' => 'LANG-DEPT', 'name' => 'Languages Department',          'description' => 'Languages Programme'],
        ];

        $deptMap = []; // code => department_id
        foreach ($deptDefs as $d) {
            $dept = Department::updateOrCreate(
                ['school_id' => $schoolId, 'code' => $d['code']],
                [
                    'name'        => $d['name'],
                    'description' => $d['description'],
                    'type'        => 'academic',
                    'is_active'   => true,
                ]
            );
            $deptMap[$d['code']] = $dept->id;
        }

        // ================================================================
        // SUBJECTS
        // Columns: name, code, type, category, level, dept_code, has_practical, credit_hours
        // level = null means universal (spans multiple levels)
        // ================================================================
        $subjects = [
            // ── UNIVERSAL (appear across multiple levels) ─────────────────
            ['English Language',              'ENG',       'core',     'language',       null,             null,          false, 4],
            ['Mathematics',                   'MATH',      'core',     'mathematics',    null,             null,          false, 4],
            ['Ghanaian Language',             'GHLANG',    'core',     'language',       null,             'LANG-DEPT',   false, 2],
            ['Creative Arts',                 'CRTS',      'core',     'arts',           null,             null,          true,  2],
            ['Religious and Moral Education', 'RME',       'core',     'religious',      null,             null,          false, 2],
            ['Physical Education',            'PE',        'core',     'physical_edu',   null,             null,          false, 2],
            ['Computing / ICT',               'COMP',      'core',     'technology',     null,             null,          true,  2],
            ['French',                        'FRENCH',    'elective', 'language',       null,             'LANG-DEPT',   false, 2],
            ['Integrated Science',            'ISCI',      'core',     'science',        null,             'SCI-DEPT',    true,  3],
            ['Social Studies',                'SOC-STUD',  'core',     'social_studies', null,             null,          false, 3],
            ['History',                       'HIST',      'core',     'social_studies', null,             'HUM-DEPT',    false, 2],

            // ── EARLY CHILDHOOD (Creche / Nursery) ────────────────────────
            ['Language and Literacy',             'LANG-LIT',  'core', 'language',       'early_childhood', null,  false, 3],
            ['Numeracy',                          'NUMER',     'core', 'mathematics',    'early_childhood', null,  false, 3],
            ['Creative Activities',               'CREAT-ACT', 'core', 'arts',           'early_childhood', null,  true,  2],
            ['Environmental Awareness',           'ENV-AWR',   'core', 'early_childhood','early_childhood', null,  false, 2],
            ['Physical Development',              'PHY-DEV',   'core', 'physical_edu',   'early_childhood', null,  false, 2],
            ['Social and Emotional Development',  'SOC-EMO',   'core', 'early_childhood','early_childhood', null,  false, 2],
            ['Music, Movement and Dance',         'MMD',       'core', 'performing_arts','early_childhood', null,  false, 2],
            ['Personal Hygiene and Self-care',    'HYGIENE',   'core', 'early_childhood','early_childhood', null,  false, 1],

            // ── PRIMARY (P1–P6) ───────────────────────────────────────────
            ['Our World and Our People', 'OWOP', 'core', 'social_studies', 'primary', null, false, 2],
            ['Science',                  'SCI',  'core', 'science',        'primary', null, true,  3],

            // ── JHS (JHS 1–3) ─────────────────────────────────────────────
            ['Basic Design and Technology', 'BDT',      'core', 'vocational',     'jhs', null, true,  3],
            ['Music and Dance',             'MUS-DANCE','core', 'performing_arts','jhs', null, false, 2],

            // ── SHS CORE ──────────────────────────────────────────────────
            ['Core Mathematics', 'CORE-MATH', 'core', 'mathematics', 'shs', null, false, 4],

            // ── SHS — SCIENCE DEPARTMENT ──────────────────────────────────
            ['Physics',            'PHYS',    'elective', 'science',     'shs', 'SCI-DEPT',  true,  3],
            ['Chemistry',          'CHEM',    'elective', 'science',     'shs', 'SCI-DEPT',  true,  3],
            ['Biology',            'BIO',     'elective', 'science',     'shs', 'SCI-DEPT',  true,  3],
            ['Elective Mathematics','ELMATH', 'elective', 'mathematics', 'shs', 'SCI-DEPT',  false, 3],
            ['Computer Science',   'CSCI',    'elective', 'technology',  'shs', 'SCI-DEPT',  true,  3],

            // ── SHS — AGRICULTURAL SCIENCE DEPT. ─────────────────────────
            ['Agricultural Science', 'AGRI-SCI', 'elective', 'science', 'shs', 'AGRI-DEPT', true, 3],

            // ── SHS — HUMANITIES DEPARTMENT ───────────────────────────────
            ['Government',           'GOVT',    'elective', 'social_studies', 'shs', 'HUM-DEPT', false, 3],
            ['Geography',            'GEOG',    'elective', 'social_studies', 'shs', 'HUM-DEPT', true,  3],
            ['Economics',            'ECON',    'elective', 'social_studies', 'shs', 'HUM-DEPT', false, 3],
            ['Literature in English','LIT-ENG', 'elective', 'language',       'shs', 'HUM-DEPT', false, 3],
            ['Religious Studies',    'REL-STUD','elective', 'religious',       'shs', 'HUM-DEPT', false, 2],

            // ── SHS — BUSINESS DEPARTMENT ─────────────────────────────────
            ['Accounting',         'ACC',      'elective', 'business', 'shs', 'BUS-DEPT', false, 3],
            ['Business Management','BUS-MGT',  'elective', 'business', 'shs', 'BUS-DEPT', false, 3],
            ['Business Mathematics','BUS-MATH','elective', 'mathematics','shs','BUS-DEPT', false, 3],
            ['Cost Accounting',    'COST-ACC', 'elective', 'business', 'shs', 'BUS-DEPT', false, 3],

            // ── SHS — HOME ECONOMICS DEPARTMENT ──────────────────────────
            ['Food and Nutrition',    'FOOD-NUT', 'elective', 'home_economics', 'shs', 'HEC-DEPT', true,  3],
            ['Management in Living',  'MGT-LIV',  'elective', 'home_economics', 'shs', 'HEC-DEPT', false, 3],
            ['Clothing and Textiles', 'CLO-TEX',  'elective', 'home_economics', 'shs', 'HEC-DEPT', true,  3],

            // ── SHS — VISUAL ARTS DEPARTMENT ─────────────────────────────
            ['Graphic Design', 'GR-DES',   'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],
            ['Picture Making', 'PIC-MK',   'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],
            ['Sculpture',      'SCULPT',   'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],
            ['Ceramics',       'CERAM',    'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],
            ['Textiles',       'TEXTILES', 'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],
            ['Basketry',       'BSKT',     'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],
            ['Leatherwork',    'LTHR',     'elective', 'visual_arts', 'shs', 'VART-DEPT', true, 3],

            // ── SHS — PERFORMING ARTS DEPARTMENT ─────────────────────────
            ['Music',           'MUS',      'elective', 'performing_arts', 'shs', 'PART-DEPT', false, 3],
            ['Dance',           'DANCE',    'elective', 'performing_arts', 'shs', 'PART-DEPT', false, 3],
            ['Drama',           'DRAMA',    'elective', 'performing_arts', 'shs', 'PART-DEPT', false, 3],
            ['Performing Arts', 'PERF-ART', 'elective', 'performing_arts', 'shs', 'PART-DEPT', false, 3],

            // ── SHS — TECHNICAL / ENGINEERING DEPARTMENT ─────────────────
            ['Technical Drawing',          'TECH-DRW', 'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Engineering',                'ENGR',     'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Manufacturing Engineering',  'MFG-ENGR', 'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Woodwork',                   'WOOD',     'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Metalwork',                  'METAL',    'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Building Construction',      'BUILD',    'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Auto Mechanics',             'AUTO-MECH','elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Electrical and Electronics', 'ELEC',     'elective', 'technical', 'shs', 'TECH-DEPT', true, 3],
            ['Robotics',                   'ROBOT',    'elective', 'technology','shs', 'TECH-DEPT', true, 3],

            // ── SHS — LANGUAGES DEPARTMENT ───────────────────────────────
            ['Spanish', 'SPANISH', 'elective', 'language', 'shs', 'LANG-DEPT', false, 3],
            ['Arabic',  'ARABIC',  'elective', 'language', 'shs', 'LANG-DEPT', false, 3],
        ];

        $created = 0;
        $updated = 0;

        foreach ($subjects as [$name, $code, $type, $category, $level, $deptCode, $hasPractical, $creditHours]) {
            $data = [
                'name'          => $name,
                'type'          => $type,
                'category'      => $category,
                'level'         => $level,
                'has_practical' => $hasPractical,
                'credit_hours'  => $creditHours,
                'department_id' => $deptCode ? ($deptMap[$deptCode] ?? null) : null,
                'is_active'     => true,
            ];

            $existing = Subject::where('school_id', $schoolId)->where('code', $code)->first();

            if ($existing) {
                $existing->update($data);
                $updated++;
            } else {
                Subject::create(array_merge($data, ['school_id' => $schoolId, 'code' => $code]));
                $created++;
            }
        }

        $this->command->info("Curriculum seeded: {$created} created, {$updated} updated.");
        $this->command->info('Departments created: ' . count($deptDefs));
    }
}
