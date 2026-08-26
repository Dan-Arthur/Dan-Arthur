<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $sid = 1; // Greenfield Academy
        $now = now()->toDateTimeString();

        $admin     = DB::table('users')->where('email', 'admin@achimota.edu.gh')->value('id');
        $teacher   = DB::table('users')->where('email', 'teacher@achimota.edu.gh')->value('id');
        $principal = DB::table('users')->where('email', 'principal@achimota.edu.gh')->value('id');
        $yearId    = DB::table('academic_years')->where('school_id', $sid)->value('id');
        $termId    = DB::table('terms')->where('academic_year_id', $yearId)->value('id');

        // ── DEPARTMENTS ───────────────────────────────────────────────────────
        $deptIds = [];
        foreach ([
            ['name' => 'Sciences',   'code' => 'SCI'],
            ['name' => 'Humanities', 'code' => 'HUM'],
            ['name' => 'Arts',       'code' => 'ART'],
            ['name' => 'Commercial', 'code' => 'COM'],
        ] as $d) {
            $deptIds[] = DB::table('departments')->insertGetId([
                'school_id'  => $sid, 'name' => $d['name'], 'code' => $d['code'],
                'is_active'  => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── POSITIONS ────────────────────────────────────────────────────────
        $posIds = [];
        foreach ([
            ['title' => 'Principal',       'type' => 'management',   'dept' => 0],
            ['title' => 'Subject Teacher', 'type' => 'teaching',     'dept' => 0],
            ['title' => 'Vice Principal',  'type' => 'management',   'dept' => 1],
            ['title' => 'Librarian',       'type' => 'non_teaching', 'dept' => 2],
        ] as $p) {
            $posIds[] = DB::table('positions')->insertGetId([
                'school_id'     => $sid, 'department_id' => $deptIds[$p['dept']],
                'title'         => $p['title'], 'type' => $p['type'],
                'is_active'     => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── SCHOOL CLASSES ────────────────────────────────────────────────────
        $classIds = [];
        foreach ([
            ['name' => 'JSS 1', 'code' => 'JSS1', 'level' => 7,  'dept' => 0],
            ['name' => 'JSS 2', 'code' => 'JSS2', 'level' => 8,  'dept' => 0],
            ['name' => 'SS 1',  'code' => 'SS1',  'level' => 10, 'dept' => 0],
            ['name' => 'SS 2',  'code' => 'SS2',  'level' => 11, 'dept' => 0],
        ] as $c) {
            $classIds[] = DB::table('school_classes')->insertGetId([
                'school_id'       => $sid, 'department_id' => $deptIds[$c['dept']],
                'name'            => $c['name'], 'code' => $c['code'], 'level' => $c['level'],
                'capacity'        => 40, 'class_teacher_id' => $teacher,
                'is_active'       => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── SUBJECTS ─────────────────────────────────────────────────────────
        $subjectIds = [];
        foreach ([
            ['name' => 'Mathematics',     'code' => 'MTH', 'dept' => 0],
            ['name' => 'English Language','code' => 'ENG', 'dept' => 1],
            ['name' => 'Biology',         'code' => 'BIO', 'dept' => 0],
            ['name' => 'Physics',         'code' => 'PHY', 'dept' => 0],
        ] as $s) {
            $subjectIds[] = DB::table('subjects')->insertGetId([
                'school_id'     => $sid, 'department_id' => $deptIds[$s['dept']],
                'name'          => $s['name'], 'code' => $s['code'],
                'is_active'     => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── STUDENTS ─────────────────────────────────────────────────────────
        $studentData = [
            ['first' => 'Chukwuemeka', 'last' => 'Okonkwo', 'gender' => 'male',   'dob' => '2010-03-15', 'no' => 'STU-2025-001', 'cls' => 2],
            ['first' => 'Fatima',      'last' => 'Aliyu',   'gender' => 'female', 'dob' => '2011-07-22', 'no' => 'STU-2025-002', 'cls' => 0],
            ['first' => 'Chisom',      'last' => 'Eze',     'gender' => 'female', 'dob' => '2010-11-08', 'no' => 'STU-2025-003', 'cls' => 2],
            ['first' => 'Babatunde',   'last' => 'Adeyemi', 'gender' => 'male',   'dob' => '2011-02-14', 'no' => 'STU-2025-004', 'cls' => 1],
        ];
        $studentIds = [];
        foreach ($studentData as $s) {
            $studentIds[] = DB::table('students')->insertGetId([
                'school_id'                => $sid,
                'student_number'           => $s['no'],
                'first_name'               => $s['first'],
                'last_name'                => $s['last'],
                'gender'                   => $s['gender'],
                'date_of_birth'            => $s['dob'],
                'nationality'              => 'Nigerian',
                'state_of_origin'          => 'Lagos',
                'admission_date'           => '2024-09-02',
                'current_class_id'         => $classIds[$s['cls']],
                'current_academic_year_id' => $yearId,
                'status'                   => 'active',
                'created_at'               => $now,
                'updated_at'               => $now,
            ]);
        }

        // ── GUARDIANS ────────────────────────────────────────────────────────
        $guardianData = [
            ['title' => 'Mr',  'first' => 'Emmanuel', 'last' => 'Okonkwo', 'phone' => '08012345671', 'email' => 'e.okonkwo@gmail.com',  'rel' => 'Father'],
            ['title' => 'Mrs', 'first' => 'Hauwa',    'last' => 'Aliyu',   'phone' => '08012345672', 'email' => 'hauwa.a@gmail.com',    'rel' => 'Mother'],
            ['title' => 'Mr',  'first' => 'Patrick',  'last' => 'Eze',     'phone' => '08012345673', 'email' => 'p.eze@gmail.com',      'rel' => 'Father'],
            ['title' => 'Mr',  'first' => 'Adewale',  'last' => 'Adeyemi', 'phone' => '08012345674', 'email' => 'adewale.a@gmail.com',  'rel' => 'Father'],
        ];
        $guardianIds = [];
        foreach ($guardianData as $g) {
            $guardianIds[] = DB::table('guardians')->insertGetId([
                'school_id'          => $sid, 'title' => $g['title'],
                'first_name'         => $g['first'], 'last_name' => $g['last'],
                'relationship'       => $g['rel'], 'phone' => $g['phone'],
                'email'              => $g['email'], 'nationality' => 'Nigerian',
                'is_primary_contact' => 1, 'is_emergency_contact' => 1,
                'portal_access'      => 0, 'status' => 'active',
                'created_at'         => $now, 'updated_at' => $now,
            ]);
        }

        // Link students ↔ guardians
        foreach ($studentIds as $i => $sId) {
            DB::table('student_guardians')->insert([
                'student_id'       => $sId, 'guardian_id' => $guardianIds[$i],
                'relationship'     => $guardianData[$i]['rel'],
                'is_primary'       => 1, 'is_emergency' => 1,
                'can_pickup'       => 1, 'receives_reports' => 1,
                'receives_invoices'=> 1,
                'created_at'       => $now, 'updated_at' => $now,
            ]);
        }

        // ── ADMISSIONS ────────────────────────────────────────────────────────
        $admissionData = [
            ['first' => 'Oluwafemi', 'last' => 'Adeleke', 'gender' => 'male',   'for' => 'JSS 1', 'status' => 'accepted'],
            ['first' => 'Amina',     'last' => 'Yusuf',   'gender' => 'female', 'for' => 'JSS 2', 'status' => 'submitted'],
            ['first' => 'Kelechi',   'last' => 'Nduka',   'gender' => 'male',   'for' => 'SS 1',  'status' => 'under_review'],
            ['first' => 'Ngozi',     'last' => 'Fashola', 'gender' => 'female', 'for' => 'JSS 1', 'status' => 'rejected'],
        ];
        foreach ($admissionData as $i => $a) {
            DB::table('admissions')->insert([
                'school_id'          => $sid,
                'academic_year_id'   => $yearId,
                'application_number' => 'APP-2025-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'first_name'         => $a['first'],
                'last_name'          => $a['last'],
                'gender'             => $a['gender'],
                'applying_for_class' => $a['for'],
                'status'             => $a['status'],
                'application_date'   => '2024-08-01',
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ── ENROLMENTS ────────────────────────────────────────────────────────
        foreach ($studentIds as $i => $sId) {
            DB::table('enrolments')->insert([
                'student_id'       => $sId,
                'class_id'         => $classIds[$studentData[$i]['cls']],
                'academic_year_id' => $yearId,
                'term_id'          => $termId,
                'roll_number'      => str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'status'           => 'active',
                'enrolled_date'    => '2024-09-02',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── EMPLOYEES ─────────────────────────────────────────────────────────
        $employeeData = [
            ['first' => 'Adaeze',  'last' => 'Nwosu',  'gender' => 'female', 'no' => 'EMP-001', 'pos' => 1, 'dept' => 0, 'salary' => 180000, 'user' => $teacher],
            ['first' => 'Ibrahim', 'last' => 'Musa',   'gender' => 'male',   'no' => 'EMP-002', 'pos' => 1, 'dept' => 1, 'salary' => 175000, 'user' => null],
            ['first' => 'Ngozi',   'last' => 'Okafor', 'gender' => 'female', 'no' => 'EMP-003', 'pos' => 3, 'dept' => 2, 'salary' => 150000, 'user' => null],
            ['first' => 'Emeka',   'last' => 'Chukwu', 'gender' => 'male',   'no' => 'EMP-004', 'pos' => 1, 'dept' => 0, 'salary' => 170000, 'user' => null],
        ];
        $employeeIds = [];
        foreach ($employeeData as $e) {
            $employeeIds[] = DB::table('employees')->insertGetId([
                'school_id'       => $sid,
                'user_id'         => $e['user'],
                'position_id'     => $posIds[$e['pos']],
                'department_id'   => $deptIds[$e['dept']],
                'employee_number' => $e['no'],
                'first_name'      => $e['first'],
                'last_name'       => $e['last'],
                'gender'          => $e['gender'],
                'phone'           => '0801000000' . substr($e['no'], -1),
                'email'           => strtolower($e['first']) . '.' . strtolower($e['last']) . '@greenfieldacademy.edu.ng',
                'employment_type' => 'full_time',
                'joining_date'    => '2022-01-10',
                'basic_salary'    => $e['salary'],
                'status'          => 'active',
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        // ── LEAVE TYPES ───────────────────────────────────────────────────────
        $leaveTypeIds = [];
        foreach ([
            ['name' => 'Annual Leave',    'days' => 21, 'paid' => 1],
            ['name' => 'Sick Leave',      'days' => 14, 'paid' => 1],
            ['name' => 'Maternity Leave', 'days' => 90, 'paid' => 1],
            ['name' => 'Emergency Leave', 'days' => 5,  'paid' => 0],
        ] as $lt) {
            $leaveTypeIds[] = DB::table('leave_types')->insertGetId([
                'school_id'         => $sid, 'name' => $lt['name'],
                'days_allowed'      => $lt['days'], 'is_paid' => $lt['paid'],
                'requires_approval' => 1,
                'created_at'        => $now, 'updated_at' => $now,
            ]);
        }

        // ── LEAVE REQUESTS ────────────────────────────────────────────────────
        $leaveData = [
            ['emp' => 0, 'type' => 0, 'start' => '2025-02-03', 'end' => '2025-02-07', 'status' => 'approved',  'by' => $admin],
            ['emp' => 1, 'type' => 1, 'start' => '2025-01-13', 'end' => '2025-01-15', 'status' => 'approved',  'by' => $admin],
            ['emp' => 2, 'type' => 3, 'start' => '2025-02-17', 'end' => '2025-02-19', 'status' => 'pending',   'by' => null],
            ['emp' => 3, 'type' => 0, 'start' => '2025-03-03', 'end' => '2025-03-07', 'status' => 'pending',   'by' => null],
        ];
        foreach ($leaveData as $l) {
            DB::table('leave_requests')->insert([
                'school_id'      => $sid,
                'employee_id'    => $employeeIds[$l['emp']],
                'leave_type_id'  => $leaveTypeIds[$l['type']],
                'start_date'     => $l['start'],
                'end_date'       => $l['end'],
                'days_requested' => 5,
                'reason'         => 'Personal reasons',
                'status'         => $l['status'],
                'approved_by'    => $l['by'],
                'actioned_at'    => $l['by'] ? $now : null,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }

        // ── FEE CATEGORIES ────────────────────────────────────────────────────
        $feeCatIds = [];
        foreach ([
            ['name' => 'Tuition Fee',       'code' => 'TUI'],
            ['name' => 'Development Levy',  'code' => 'DEV'],
            ['name' => 'Examination Fee',   'code' => 'EXM'],
            ['name' => 'Books & Materials', 'code' => 'BKS'],
        ] as $i => $fc) {
            $feeCatIds[] = DB::table('fee_categories')->insertGetId([
                'school_id'  => $sid, 'name' => $fc['name'], 'code' => $fc['code'],
                'is_active'  => 1, 'sort_order' => $i,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── FEE STRUCTURE ─────────────────────────────────────────────────────
        $feeStructId = DB::table('fee_structures')->insertGetId([
            'school_id'              => $sid, 'academic_year_id' => $yearId, 'term_id' => $termId,
            'name'                   => 'First Term Fees 2024/2025',
            'applies_to_all_classes' => 1, 'is_active' => 1,
            'created_at'             => $now, 'updated_at' => $now,
        ]);
        foreach ([45000, 10000, 8000, 12000] as $i => $amt) {
            DB::table('fee_structure_items')->insert([
                'fee_structure_id' => $feeStructId,
                'fee_category_id'  => $feeCatIds[$i],
                'name'             => ['Tuition Fee', 'Development Levy', 'Examination Fee', 'Books & Materials'][$i],
                'amount'           => $amt,
                'is_mandatory'     => 1,
                'sort_order'       => $i,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── INVOICES & PAYMENTS ───────────────────────────────────────────────
        $invoiceIds = [];
        $total = 75000;
        foreach ($studentIds as $i => $sId) {
            $paid    = [75000, 45000, 0, 30000][$i];
            $status  = ['paid', 'partial', 'unpaid', 'partial'][$i];
            $invoiceIds[] = DB::table('invoices')->insertGetId([
                'school_id'          => $sid, 'student_id' => $sId,
                'academic_year_id'   => $yearId, 'term_id' => $termId,
                'fee_structure_id'   => $feeStructId,
                'invoice_number'     => 'INV-2025-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'issue_date'         => '2025-01-06',
                'due_date'           => '2025-01-31',
                'subtotal'           => $total, 'discount_amount' => 0,
                'scholarship_amount' => 0, 'total_amount' => $total,
                'amount_paid'        => $paid, 'balance' => $total - $paid,
                'status'             => $status, 'created_by' => $admin,
                'created_at'         => $now, 'updated_at' => $now,
            ]);
        }
        foreach ([
            ['inv' => 0, 'sid' => 0, 'amt' => 75000, 'method' => 'bank_transfer', 'ref' => 'TRF20250001', 'no' => 'PAY-2025-001'],
            ['inv' => 1, 'sid' => 1, 'amt' => 45000, 'method' => 'cash',          'ref' => null,          'no' => 'PAY-2025-002'],
            ['inv' => 3, 'sid' => 3, 'amt' => 30000, 'method' => 'pos',           'ref' => 'POS20250001', 'no' => 'PAY-2025-003'],
            ['inv' => 1, 'sid' => 1, 'amt' => 20000, 'method' => 'bank_transfer', 'ref' => 'TRF20250002', 'no' => 'PAY-2025-004'],
        ] as $p) {
            DB::table('payments')->insert([
                'school_id'        => $sid,
                'invoice_id'       => $invoiceIds[$p['inv']],
                'student_id'       => $studentIds[$p['sid']],
                'payment_number'   => $p['no'],
                'amount'           => $p['amt'],
                'payment_date'     => '2025-01-10',
                'payment_method'   => $p['method'],
                'reference_number' => $p['ref'],
                'status'           => 'confirmed',
                'received_by'      => $admin,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // ── LIBRARY ───────────────────────────────────────────────────────────
        $bookCatIds = [];
        foreach ([
            ['name' => 'Science',        'code' => 'SCI'],
            ['name' => 'Language Arts',  'code' => 'LAN'],
            ['name' => 'Mathematics',    'code' => 'MAT'],
            ['name' => 'Social Studies', 'code' => 'SOC'],
        ] as $bc) {
            $bookCatIds[] = DB::table('book_categories')->insertGetId([
                'school_id' => $sid, 'name' => $bc['name'], 'code' => $bc['code'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $pubIds = [];
        foreach (['Longman Nigeria', 'Evans Brothers', 'University Press', 'Oxford University Press'] as $pub) {
            $pubIds[] = DB::table('publishers')->insertGetId([
                'school_id' => $sid, 'name' => $pub, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $authorIds = [];
        foreach (['Macrae Frank', 'Ayo Bamgbose', 'O.A. Nwoke', 'M.W. Anyakoha'] as $auth) {
            $authorIds[] = DB::table('authors')->insertGetId([
                'school_id' => $sid, 'name' => $auth, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $bookData = [
            ['title' => 'Biology for Senior Schools',    'isbn' => '978-0-582-60335-8', 'cat' => 0, 'pub' => 0, 'year' => 2020, 'copies' => 25, 'shelf' => 'A1', 'auth' => 2],
            ['title' => 'Essential Mathematics for JSS', 'isbn' => '978-0-582-60447-7', 'cat' => 2, 'pub' => 2, 'year' => 2019, 'copies' => 30, 'shelf' => 'B1', 'auth' => 0],
            ['title' => 'Oral English for Schools',      'isbn' => '978-978-902-100-5', 'cat' => 1, 'pub' => 1, 'year' => 2021, 'copies' => 20, 'shelf' => 'C1', 'auth' => 1],
            ['title' => 'New School Physics',            'isbn' => '978-978-048-148-7', 'cat' => 0, 'pub' => 2, 'year' => 2022, 'copies' => 15, 'shelf' => 'A2', 'auth' => 3],
        ];
        foreach ($bookData as $i => $b) {
            $bookId = DB::table('books')->insertGetId([
                'school_id'        => $sid, 'category_id' => $bookCatIds[$b['cat']],
                'publisher_id'     => $pubIds[$b['pub']], 'title' => $b['title'],
                'isbn'             => $b['isbn'], 'publish_year' => $b['year'],
                'total_copies'     => $b['copies'], 'available_copies' => $b['copies'] - 1,
                'location'         => $b['shelf'], 'is_active' => 1,
                'created_at'       => $now, 'updated_at' => $now,
            ]);
            DB::table('book_authors')->insert(['book_id' => $bookId, 'author_id' => $authorIds[$b['auth']]]);
            // One loaned copy per book
            $copyId = DB::table('book_copies')->insertGetId([
                'book_id'          => $bookId,
                'barcode'          => 'BC-' . str_pad($bookId, 5, '0', STR_PAD_LEFT),
                'accession_number' => 'ACC-' . str_pad($bookId, 5, '0', STR_PAD_LEFT),
                'condition'        => 'good', 'status' => 'loaned',
                'created_at'       => $now, 'updated_at' => $now,
            ]);
            DB::table('book_loans')->insert([
                'school_id'    => $sid, 'book_copy_id' => $copyId,
                'member_type'  => 'student', 'member_id' => $studentIds[$i],
                'loan_date'    => '2025-01-15', 'due_date' => '2025-02-15',
                'status'       => 'active', 'fine_amount' => 0, 'fine_paid' => 0,
                'issued_by'    => $admin,
                'created_at'   => $now, 'updated_at' => $now,
            ]);
        }

        // ── VEHICLES ──────────────────────────────────────────────────────────
        $vehicleIds = [];
        foreach ([
            ['reg' => 'ABC-123-EK', 'make' => 'Toyota',   'model' => 'Coaster',  'cap' => 28, 'color' => 'Yellow'],
            ['reg' => 'XYZ-456-LA', 'make' => 'Mercedes', 'model' => 'Sprinter', 'cap' => 18, 'color' => 'White'],
            ['reg' => 'MNO-789-AB', 'make' => 'Nissan',   'model' => 'Urvan',    'cap' => 15, 'color' => 'Blue'],
            ['reg' => 'PQR-321-OG', 'make' => 'Toyota',   'model' => 'Hiace',    'cap' => 14, 'color' => 'White'],
        ] as $v) {
            $vehicleIds[] = DB::table('vehicles')->insertGetId([
                'school_id'           => $sid,
                'registration_number' => $v['reg'],
                'make'                => $v['make'], 'model' => $v['model'],
                'year'                => 2020, 'color' => $v['color'],
                'capacity'            => $v['cap'], 'type' => 'bus', 'status' => 'active',
                'insurance_expiry'    => '2025-12-31',
                'created_at'          => $now, 'updated_at' => $now,
            ]);
        }

        // ── DRIVERS ───────────────────────────────────────────────────────────
        $driverIds = [];
        foreach ($employeeIds as $i => $empId) {
            $driverIds[] = DB::table('drivers')->insertGetId([
                'school_id'      => $sid, 'employee_id' => $empId,
                'licence_number' => 'DRV-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'licence_class'  => 'C', 'licence_expiry' => '2026-06-30',
                'status'         => 'active',
                'created_at'     => $now, 'updated_at' => $now,
            ]);
        }

        // ── TRANSPORT ROUTES + STOPS + ASSIGNMENTS ────────────────────────────
        $routeData = [
            ['name' => 'Ikeja Route',    'code' => 'IKJ', 'fee' => 12000],
            ['name' => 'Surulere Route', 'code' => 'SUR', 'fee' => 10000],
            ['name' => 'Lekki Route',    'code' => 'LEK', 'fee' => 15000],
            ['name' => 'Yaba Route',     'code' => 'YAB', 'fee' => 9000],
        ];
        foreach ($routeData as $i => $r) {
            $routeId = DB::table('transport_routes')->insertGetId([
                'school_id'   => $sid, 'vehicle_id' => $vehicleIds[$i],
                'driver_id'   => $driverIds[$i], 'name' => $r['name'],
                'code'        => $r['code'], 'direction' => 'both',
                'monthly_fee' => $r['fee'], 'is_active' => 1,
                'created_at'  => $now, 'updated_at' => $now,
            ]);
            $stopId = DB::table('transport_stops')->insertGetId([
                'route_id'    => $routeId, 'name' => 'Main Stop – ' . $r['name'],
                'address'     => 'Main Bus Stop, ' . $r['name'],
                'pickup_time' => '06:30:00', 'dropoff_time' => '15:30:00',
                'sequence'    => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('student_transport')->insert([
                'student_id'       => $studentIds[$i], 'route_id' => $routeId,
                'stop_id'          => $stopId, 'academic_year_id' => $yearId,
                'direction'        => 'both', 'status' => 'active',
                'created_at'       => $now, 'updated_at' => $now,
            ]);
        }

        // ── INVENTORY ─────────────────────────────────────────────────────────
        $assetCatIds = [];
        foreach ([
            ['name' => 'Electronics',     'code' => 'ELC'],
            ['name' => 'Furniture',       'code' => 'FUR'],
            ['name' => 'Lab Equipment',   'code' => 'LAB'],
            ['name' => 'Office Supplies', 'code' => 'OFC'],
        ] as $ac) {
            $assetCatIds[] = DB::table('asset_categories')->insertGetId([
                'school_id'  => $sid, 'name' => $ac['name'], 'code' => $ac['code'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $supplierIds = [];
        foreach ([
            ['name' => 'TechPlus Nigeria',  'contact' => 'Mr Eze',     'phone' => '08055000001'],
            ['name' => 'OfficeHub Ltd',     'contact' => 'Mrs Bello',  'phone' => '08055000002'],
            ['name' => 'SchoMart Supplies', 'contact' => 'Mr Okafor',  'phone' => '08055000003'],
            ['name' => 'LabEquip Co.',      'contact' => 'Dr Ibrahim', 'phone' => '08055000004'],
        ] as $sup) {
            $supplierIds[] = DB::table('suppliers')->insertGetId([
                'school_id'      => $sid, 'name' => $sup['name'],
                'contact_person' => $sup['contact'], 'phone' => $sup['phone'],
                'is_active'      => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        foreach ([
            ['name' => 'Dell Latitude Laptop',  'tag' => 'AST-001', 'cat' => 0, 'sup' => 0, 'price' => 850000],
            ['name' => 'Teacher\'s Wooden Desk', 'tag' => 'AST-002', 'cat' => 1, 'sup' => 1, 'price' => 45000],
            ['name' => 'Binocular Microscope',  'tag' => 'AST-003', 'cat' => 2, 'sup' => 3, 'price' => 280000],
            ['name' => 'HP LaserJet Printer',   'tag' => 'AST-004', 'cat' => 0, 'sup' => 0, 'price' => 155000],
        ] as $a) {
            DB::table('assets')->insert([
                'school_id'      => $sid, 'category_id' => $assetCatIds[$a['cat']],
                'supplier_id'    => $supplierIds[$a['sup']], 'name' => $a['name'],
                'asset_tag'      => $a['tag'], 'purchase_price' => $a['price'],
                'purchase_date'  => '2024-08-15', 'condition' => 'good', 'status' => 'active',
                'created_at'     => $now, 'updated_at' => $now,
            ]);
        }
        $invItemIds = [];
        foreach ([
            ['name' => 'White Chalk (Box)',       'sku' => 'INV-001', 'unit' => 'box',   'qty' => 50,  'reorder' => 10, 'cost' => 500],
            ['name' => 'Whiteboard Marker',        'sku' => 'INV-002', 'unit' => 'piece', 'qty' => 120, 'reorder' => 20, 'cost' => 350],
            ['name' => 'Exercise Book (200 pgs)', 'sku' => 'INV-003', 'unit' => 'piece', 'qty' => 400, 'reorder' => 50, 'cost' => 250],
            ['name' => 'Biro Pen (Blue)',          'sku' => 'INV-004', 'unit' => 'piece', 'qty' => 300, 'reorder' => 30, 'cost' => 100],
        ] as $item) {
            $itemId = DB::table('inventory_items')->insertGetId([
                'school_id'         => $sid, 'category_id' => $assetCatIds[3],
                'name'              => $item['name'], 'sku' => $item['sku'],
                'unit'              => $item['unit'], 'quantity_in_stock' => $item['qty'],
                'reorder_level'     => $item['reorder'], 'unit_cost' => $item['cost'],
                'is_active'         => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $invItemIds[] = $itemId;
            DB::table('stock_movements')->insert([
                'school_id'         => $sid, 'inventory_item_id' => $itemId,
                'supplier_id'       => $supplierIds[2],
                'movement_type'     => 'purchase', 'quantity' => $item['qty'],
                'balance_after'     => $item['qty'], 'unit_cost' => $item['cost'],
                'reference'         => 'PO-2025-' . str_pad(count($invItemIds), 3, '0', STR_PAD_LEFT),
                'notes'             => 'Initial stock purchase',
                'recorded_by'       => $admin, 'movement_date' => $now,
                'created_at'        => $now, 'updated_at' => $now,
            ]);
        }

        // ── ANNOUNCEMENTS ─────────────────────────────────────────────────────
        foreach ([
            ['title' => 'First Term Academic Calendar 2024/2025',     'type' => 'academic',   'audience' => 'all',     'status' => 'published', 'pinned' => 1],
            ['title' => 'Parent-Teacher Meeting – 25th January 2025', 'type' => 'event',      'audience' => 'parents', 'status' => 'published', 'pinned' => 0],
            ['title' => 'First Term Fee Payment Deadline',            'type' => 'fee',        'audience' => 'parents', 'status' => 'published', 'pinned' => 1],
            ['title' => 'Inter-House Sports Day – February 2025',     'type' => 'event',      'audience' => 'all',     'status' => 'draft',     'pinned' => 0],
        ] as $ann) {
            DB::table('announcements')->insert([
                'school_id'  => $sid, 'created_by' => $admin, 'title' => $ann['title'],
                'body'       => 'Detailed information regarding: ' . $ann['title'] . '. Please take note and act accordingly.',
                'type'       => $ann['type'], 'audience' => $ann['audience'],
                'is_pinned'  => $ann['pinned'], 'status' => $ann['status'],
                'publish_at' => $ann['status'] === 'published' ? now()->subDays(3)->toDateTimeString() : null,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ── EVENTS ────────────────────────────────────────────────────────────
        foreach ([
            ['title' => 'First Term Examination',         'type' => 'examination', 'start' => '2025-03-10 08:00:00', 'end' => '2025-03-21 14:00:00', 'status' => 'scheduled',  'location' => 'All Classrooms'],
            ['title' => 'Inter-House Sports Competition', 'type' => 'sport',       'start' => '2025-02-14 08:00:00', 'end' => '2025-02-14 17:00:00', 'status' => 'scheduled',  'location' => 'School Field'],
            ['title' => 'Staff Meeting – January 2025',  'type' => 'meeting',     'start' => '2025-01-20 14:00:00', 'end' => '2025-01-20 17:00:00', 'status' => 'completed',  'location' => 'Conference Room'],
            ['title' => 'Independence Day Holiday',      'type' => 'holiday',     'start' => '2024-10-01 00:00:00', 'end' => '2024-10-01 23:59:00', 'status' => 'completed',  'location' => ''],
        ] as $ev) {
            DB::table('events')->insert([
                'school_id'      => $sid, 'created_by' => $admin, 'title' => $ev['title'],
                'description'    => 'Details: ' . $ev['title'],
                'type'           => $ev['type'], 'audience' => 'all',
                'start_datetime' => $ev['start'], 'end_datetime' => $ev['end'],
                'all_day'        => 0, 'location' => $ev['location'],
                'status'         => $ev['status'], 'color' => '#3B82F6',
                'created_at'     => $now, 'updated_at' => $now,
            ]);
        }

        // ── MESSAGES ──────────────────────────────────────────────────────────
        foreach ([
            ['subject' => 'Welcome to the New Term',          'sender' => $admin,     'recipient' => $principal],
            ['subject' => 'Staff Meeting Reminder',           'sender' => $principal, 'recipient' => $teacher],
            ['subject' => 'Budget Approval Request',          'sender' => $admin,     'recipient' => $principal],
            ['subject' => 'Student Performance Report – SS1', 'sender' => $teacher,   'recipient' => $admin],
        ] as $msg) {
            $msgId = DB::table('messages')->insertGetId([
                'school_id'  => $sid, 'sender_id' => $msg['sender'],
                'subject'    => $msg['subject'],
                'body'       => 'This is the body of: ' . $msg['subject'] . '. Please review at your earliest convenience.',
                'type'       => 'internal', 'status' => 'sent',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            DB::table('message_recipients')->insert([
                'message_id'   => $msgId, 'recipient_id' => $msg['recipient'],
                'is_read'      => 0, 'is_starred' => 0, 'is_deleted' => 0,
                'created_at'   => $now, 'updated_at' => $now,
            ]);
        }

        // ── GRADING SCALE ─────────────────────────────────────────────────────
        $scaleId = DB::table('grading_scales')->insertGetId([
            'school_id'  => $sid, 'name' => 'Standard Grading Scale',
            'is_default' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ([
            ['grade' => 'A', 'min' => 70, 'max' => 100, 'remark' => 'Distinction', 'gpa' => 4],
            ['grade' => 'B', 'min' => 60, 'max' => 69,  'remark' => 'Credit',      'gpa' => 3],
            ['grade' => 'C', 'min' => 50, 'max' => 59,  'remark' => 'Merit',       'gpa' => 2],
            ['grade' => 'F', 'min' => 0,  'max' => 49,  'remark' => 'Fail',        'gpa' => 0],
        ] as $gb) {
            DB::table('grade_bands')->insert([
                'grading_scale_id' => $scaleId,
                'grade'            => $gb['grade'], 'min_score' => $gb['min'],
                'max_score'        => $gb['max'], 'remark' => $gb['remark'],
                'gpa_point'        => $gb['gpa'],
                'created_at'       => $now, 'updated_at' => $now,
            ]);
        }

        // ── ASSESSMENTS + MARKS ───────────────────────────────────────────────
        $assessmentData = [
            ['title' => 'First CA – Mathematics', 'type' => 'ca',      'sub' => 0, 'cls' => 2, 'max' => 20],
            ['title' => 'First CA – English',      'type' => 'ca',      'sub' => 1, 'cls' => 2, 'max' => 20],
            ['title' => 'Biology Quiz 1',          'type' => 'quiz',    'sub' => 2, 'cls' => 0, 'max' => 10],
            ['title' => 'Physics Mid-Term Exam',   'type' => 'mid_term','sub' => 3, 'cls' => 2, 'max' => 50],
        ];
        $ss1Students = [$studentIds[0], $studentIds[2]]; // students in SS 1 (cls index 2)
        $jss1Students = [$studentIds[1]];                // student in JSS 1 (cls index 0)
        foreach ($assessmentData as $i => $ass) {
            $assId = DB::table('assessments')->insertGetId([
                'school_id'        => $sid,
                'class_id'         => $classIds[$ass['cls']],
                'subject_id'       => $subjectIds[$ass['sub']],
                'teacher_id'       => $teacher,
                'academic_year_id' => $yearId,
                'term_id'          => $termId,
                'title'            => $ass['title'],
                'type'             => $ass['type'],
                'max_score'        => $ass['max'],
                'assessment_date'  => '2025-01-' . str_pad(15 + $i, 2, '0', STR_PAD_LEFT),
                'status'           => 'completed',
                'marks_entered'    => 1,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            // Enter marks for enrolled students
            $eligible = $ass['cls'] === 0 ? $jss1Students : $ss1Students;
            foreach ($eligible as $j => $sId) {
                DB::table('marks')->insert([
                    'assessment_id' => $assId,
                    'student_id'    => $sId,
                    'score'         => min($ass['max'], round($ass['max'] * 0.75) + $j * 2),
                    'is_absent'     => 0, 'is_exempt' => 0,
                    'entered_by'    => $teacher, 'entered_at' => $now,
                    'created_at'    => $now, 'updated_at' => $now,
                ]);
            }
        }

        $this->command->info('✓ Demo data seeded successfully across all modules.');
    }
}
