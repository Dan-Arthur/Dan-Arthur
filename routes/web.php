<?php

use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EnrolmentController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DisciplinaryRecordController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\GradingScaleController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\StaffAttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SmsAlertController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PARENT / GUARDIAN PORTAL
// ============================================================
Route::middleware(['auth', 'guardian.portal'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/children/{studentId}', [PortalController::class, 'child'])->name('child');
    Route::get('/invoices/{invoiceId}', [PortalController::class, 'invoice'])->name('invoice');
    Route::get('/results/{resultId}', [PortalController::class, 'result'])->name('result');
});

// ============================================================
// GUEST ROUTES
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginController::class)->name('login');
    Route::post('/login', LoginController::class);
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
});

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================
Route::middleware(['auth'])->group(function () {

    // Sign out
    Route::post('/logout', LogoutController::class)->name('logout');

    // Dashboard
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/dashboard', DashboardController::class)->name('dashboard.alt');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ============================================================
    // STUDENT MANAGEMENT
    // ============================================================
    Route::get('students/search', [StudentController::class, 'search'])->name('students.search');
    Route::resource('students', StudentController::class)
        ->middleware('can:view students,create students,edit students,delete students');

    // ============================================================
    // USER & ACCESS MANAGEMENT
    // ============================================================
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
    Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])
        ->name('users.reset-password');

    // ============================================================
    // ADMISSIONS
    // ============================================================
    Route::resource('admissions', AdmissionController::class);
    Route::patch('admissions/{admission}/status', [AdmissionController::class, 'updateStatus'])
        ->name('admissions.update-status');
    Route::post('admissions/{admission}/enrol', [AdmissionController::class, 'enrol'])
        ->name('admissions.enrol');

    // ============================================================
    // GUARDIANS
    // ============================================================
    Route::resource('guardians', GuardianController::class);
    Route::patch('guardians/{guardian}/toggle-portal', [GuardianController::class, 'togglePortal'])
        ->name('guardians.toggle-portal');

    // Student ↔ Guardian linking (nested under students)
    Route::post('students/{student}/guardians', [GuardianController::class, 'attachStudent'])
        ->name('students.guardians.attach');
    Route::delete('students/{student}/guardians/{guardian}', [GuardianController::class, 'detachStudent'])
        ->name('students.guardians.detach');
    Route::patch('students/{student}/guardians/{guardian}', [GuardianController::class, 'updatePivot'])
        ->name('students.guardians.update-pivot');

    // ============================================================
    // SCHOOL CLASSES
    // ============================================================
    Route::resource('classes', ClassController::class);
    Route::patch('classes/{class}/toggle-active', [ClassController::class, 'toggleActive'])
        ->name('classes.toggle-active');

    // ============================================================
    // ENROLMENTS
    // ============================================================
    Route::resource('enrolments', EnrolmentController::class);
    Route::patch('enrolments/{enrolment}/withdraw', [EnrolmentController::class, 'withdraw'])
        ->name('enrolments.withdraw');
    Route::get('enrolments-bulk', [EnrolmentController::class, 'bulk'])
        ->name('enrolments.bulk');
    Route::post('enrolments-bulk', [EnrolmentController::class, 'bulkStore'])
        ->name('enrolments.bulk.store');
    Route::get('enrolments-promote', [EnrolmentController::class, 'promote'])
        ->name('enrolments.promote');
    Route::post('enrolments-promote', [EnrolmentController::class, 'promoteStore'])
        ->name('enrolments.promote.store');

    // ============================================================
    // SETTINGS
    // ============================================================
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('settings/school', [SettingsController::class, 'updateSchool'])->name('settings.school.update');
    Route::put('settings/system', [SettingsController::class, 'updateSystemSettings'])->name('settings.system.update');

    // Academic years
    Route::post('settings/years', [SettingsController::class, 'storeYear'])->name('settings.years.store');
    Route::put('settings/years/{year}', [SettingsController::class, 'updateYear'])->name('settings.years.update');
    Route::patch('settings/years/{year}/set-current', [SettingsController::class, 'setCurrentYear'])->name('settings.years.set-current');
    Route::delete('settings/years/{year}', [SettingsController::class, 'destroyYear'])->name('settings.years.destroy');

    // Terms (within a year)
    Route::post('settings/years/{year}/terms', [SettingsController::class, 'storeTerm'])->name('settings.terms.store');
    Route::put('settings/terms/{term}', [SettingsController::class, 'updateTerm'])->name('settings.terms.update');
    Route::patch('settings/terms/{term}/set-current', [SettingsController::class, 'setCurrentTerm'])->name('settings.terms.set-current');
    Route::delete('settings/terms/{term}', [SettingsController::class, 'destroyTerm'])->name('settings.terms.destroy');

    // Campuses
    Route::post('settings/campuses', [SettingsController::class, 'storeCampus'])->name('settings.campuses.store');
    Route::put('settings/campuses/{campus}', [SettingsController::class, 'updateCampus'])->name('settings.campuses.update');
    Route::delete('settings/campuses/{campus}', [SettingsController::class, 'destroyCampus'])->name('settings.campuses.destroy');

    // ============================================================
    // DEPARTMENTS
    // ============================================================
    Route::resource('departments', DepartmentController::class);
    Route::patch('departments/{department}/toggle-active', [DepartmentController::class, 'toggleActive'])
        ->name('departments.toggle-active');

    // ============================================================
    // SUBJECTS
    // ============================================================
    Route::resource('subjects', SubjectController::class);
    Route::patch('subjects/{subject}/toggle-active', [SubjectController::class, 'toggleActive'])
        ->name('subjects.toggle-active');
    Route::post('subjects/{subject}/assign', [SubjectController::class, 'assignClass'])
        ->name('subjects.assign');
    Route::patch('subjects/{subject}/assignments/{assignment}', [SubjectController::class, 'updateAssignment'])
        ->name('subjects.assignment.update');
    Route::delete('subjects/{subject}/assignments/{assignment}', [SubjectController::class, 'unassignClass'])
        ->name('subjects.unassign');

    // ============================================================
    // TIMETABLES
    // ============================================================
    // Exams
    Route::get('exams/terms', [ExamController::class, 'termsByYear'])->name('exams.terms');
    Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::put('exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');

    Route::get('timetables', [TimetableController::class, 'index'])->name('timetables.index');
    Route::get('timetables/teacher', [TimetableController::class, 'teacher'])->name('timetables.teacher');
    Route::post('timetables', [TimetableController::class, 'store'])->name('timetables.store');
    Route::put('timetables/{timetable}', [TimetableController::class, 'update'])->name('timetables.update');
    Route::delete('timetables/{timetable}', [TimetableController::class, 'destroy'])->name('timetables.destroy');
    Route::get('timetables/periods', [TimetableController::class, 'periods'])->name('timetables.periods');
    Route::post('timetables/periods', [TimetableController::class, 'storePeriod'])->name('timetables.periods.store');
    Route::put('timetables/periods/{period}', [TimetableController::class, 'updatePeriod'])->name('timetables.periods.update');
    Route::delete('timetables/periods/{period}', [TimetableController::class, 'destroyPeriod'])->name('timetables.periods.destroy');

    // ============================================================
    // ATTENDANCE
    // ============================================================
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    Route::get('staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
    Route::post('staff-attendance', [StaffAttendanceController::class, 'store'])->name('staff-attendance.store');
    Route::get('staff-attendance/report', [StaffAttendanceController::class, 'report'])->name('staff-attendance.report');

    // ============================================================
    // ASSESSMENTS
    // ============================================================
    Route::resource('assessments', AssessmentController::class);
    Route::post('assessments/{assessment}/marks', [AssessmentController::class, 'enterMarks'])
        ->name('assessments.marks');

    // ============================================================
    // RESULTS
    // ============================================================
    Route::get('results', [ResultController::class, 'index'])->name('results.index');
    Route::post('results/generate', [ResultController::class, 'generate'])->name('results.generate');
    Route::post('results/bulk-approve', [ResultController::class, 'bulkApprove'])->name('results.bulk-approve');
    Route::post('results/bulk-publish', [ResultController::class, 'bulkPublish'])->name('results.bulk-publish');
    Route::get('results/{result}', [ResultController::class, 'show'])->name('results.show');
    Route::get('results/{result}/report-card', [ResultController::class, 'reportCard'])->name('results.report-card');
    Route::put('results/{result}/comment', [ResultController::class, 'updateComment'])->name('results.comment');
    Route::patch('results/{result}/approve', [ResultController::class, 'approve'])->name('results.approve');
    Route::patch('results/{result}/publish', [ResultController::class, 'publish'])->name('results.publish');

    // ============================================================
    // DISCIPLINARY RECORDS
    // ============================================================
    Route::get('disciplinary/search-students', [DisciplinaryRecordController::class, 'searchStudents'])->name('disciplinary.search-students');
    Route::get('disciplinary/terms', [DisciplinaryRecordController::class, 'termsByYear'])->name('disciplinary.terms');
    Route::get('disciplinary', [DisciplinaryRecordController::class, 'index'])->name('disciplinary.index');
    Route::get('disciplinary/create', [DisciplinaryRecordController::class, 'create'])->name('disciplinary.create');
    Route::post('disciplinary', [DisciplinaryRecordController::class, 'store'])->name('disciplinary.store');
    Route::get('disciplinary/{disciplinary}', [DisciplinaryRecordController::class, 'show'])->name('disciplinary.show');
    Route::get('disciplinary/{disciplinary}/edit', [DisciplinaryRecordController::class, 'edit'])->name('disciplinary.edit');
    Route::put('disciplinary/{disciplinary}', [DisciplinaryRecordController::class, 'update'])->name('disciplinary.update');
    Route::delete('disciplinary/{disciplinary}', [DisciplinaryRecordController::class, 'destroy'])->name('disciplinary.destroy');
    Route::get('students/{student}/disciplinary', [DisciplinaryRecordController::class, 'studentHistory'])->name('disciplinary.student-history');

    // ============================================================
    // GRADING SCALES
    // ============================================================
    Route::get('grading-scales', [GradingScaleController::class, 'index'])->name('grading-scales.index');
    Route::get('grading-scales/create', [GradingScaleController::class, 'create'])->name('grading-scales.create');
    Route::post('grading-scales', [GradingScaleController::class, 'store'])->name('grading-scales.store');
    Route::get('grading-scales/{gradingScale}/edit', [GradingScaleController::class, 'edit'])->name('grading-scales.edit');
    Route::put('grading-scales/{gradingScale}', [GradingScaleController::class, 'update'])->name('grading-scales.update');
    Route::patch('grading-scales/{gradingScale}/set-default', [GradingScaleController::class, 'setDefault'])->name('grading-scales.set-default');
    Route::delete('grading-scales/{gradingScale}', [GradingScaleController::class, 'destroy'])->name('grading-scales.destroy');

    // ============================================================
    // SCHOLARSHIPS
    // ============================================================
    Route::get('scholarships/search-students', [ScholarshipController::class, 'searchStudents'])->name('scholarships.search-students');
    Route::get('scholarships', [ScholarshipController::class, 'index'])->name('scholarships.index');
    Route::get('scholarships/create', [ScholarshipController::class, 'create'])->name('scholarships.create');
    Route::post('scholarships', [ScholarshipController::class, 'store'])->name('scholarships.store');
    Route::get('scholarships/{scholarship}/edit', [ScholarshipController::class, 'edit'])->name('scholarships.edit');
    Route::put('scholarships/{scholarship}', [ScholarshipController::class, 'update'])->name('scholarships.update');
    Route::delete('scholarships/{scholarship}', [ScholarshipController::class, 'destroy'])->name('scholarships.destroy');
    Route::get('scholarships/{scholarship}/students', [ScholarshipController::class, 'students'])->name('scholarships.students');
    Route::post('scholarships/{scholarship}/assign', [ScholarshipController::class, 'assign'])->name('scholarships.assign');
    Route::delete('scholarships/{scholarship}/revoke/{studentScholarship}', [ScholarshipController::class, 'revoke'])->name('scholarships.revoke');

    // ============================================================
    // FINANCE: FEES
    // ============================================================
    Route::get('fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('fees/create', [FeeController::class, 'create'])->name('fees.create');
    Route::post('fees', [FeeController::class, 'store'])->name('fees.store');
    Route::post('fees/categories', [FeeController::class, 'storeCategory'])->name('fees.categories.store');
    Route::patch('fees/categories/{category}', [FeeController::class, 'updateCategory'])->name('fees.categories.update');
    Route::delete('fees/categories/{category}', [FeeController::class, 'destroyCategory'])->name('fees.categories.destroy');
    Route::get('fees/{fee}', [FeeController::class, 'show'])->name('fees.show');
    Route::get('fees/{fee}/edit', [FeeController::class, 'edit'])->name('fees.edit');
    Route::put('fees/{fee}', [FeeController::class, 'update'])->name('fees.update');
    Route::delete('fees/{fee}', [FeeController::class, 'destroy'])->name('fees.destroy');
    Route::get('fees/{fee}/items', [InvoiceController::class, 'structureItems'])->name('fees.structure-items');

    // ============================================================
    // FINANCE: INVOICES
    // ============================================================
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::patch('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');

    // ============================================================
    // FINANCE: PAYMENTS
    // ============================================================
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    Route::get('payments/{payment}/receipt-pdf', [PaymentController::class, 'receiptPdf'])->name('payments.receipt-pdf');

    // ============================================================
    // FINANCE: EXPENSES
    // ============================================================
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
    Route::patch('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::patch('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');
    Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // ============================================================
    // HR: EMPLOYEES
    // ============================================================
    Route::get('employees/search', [EmployeeController::class, 'search'])->name('employees.search');
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/positions', [EmployeeController::class, 'positions'])->name('employees.positions');
    Route::post('employees/positions', [EmployeeController::class, 'storePosition'])->name('employees.positions.store');
    Route::patch('employees/positions/{position}', [EmployeeController::class, 'updatePosition'])->name('employees.positions.update');
    Route::delete('employees/positions/{position}', [EmployeeController::class, 'destroyPosition'])->name('employees.positions.destroy');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // ============================================================
    // HR: LEAVE
    // ============================================================
    Route::get('leave', [LeaveController::class, 'index'])->name('leave.index');
    Route::get('leave/create', [LeaveController::class, 'create'])->name('leave.create');
    Route::post('leave', [LeaveController::class, 'store'])->name('leave.store');
    Route::get('leave/types', [LeaveController::class, 'types'])->name('leave.types');
    Route::post('leave/types', [LeaveController::class, 'storeType'])->name('leave.types.store');
    Route::patch('leave/types/{leaveType}', [LeaveController::class, 'updateType'])->name('leave.types.update');
    Route::delete('leave/types/{leaveType}', [LeaveController::class, 'destroyType'])->name('leave.types.destroy');
    Route::get('leave/{leave}', [LeaveController::class, 'show'])->name('leave.show');
    Route::patch('leave/{leave}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
    Route::patch('leave/{leave}/reject', [LeaveController::class, 'reject'])->name('leave.reject');
    Route::patch('leave/{leave}/cancel', [LeaveController::class, 'cancel'])->name('leave.cancel');

    // ============================================================
    // LIBRARY
    // ============================================================
    Route::get('library', [LibraryController::class, 'index'])->name('library.index');
    Route::get('library/catalogue', [LibraryController::class, 'catalogue'])->name('library.catalogue');
    Route::post('library/catalogue', [LibraryController::class, 'storeCatalogue'])->name('library.catalogue.store');
    Route::get('library/loans', [LibraryController::class, 'loans'])->name('library.loans');
    Route::patch('library/loans/{loan}/return', [LibraryController::class, 'returnBook'])->name('library.loans.return');
    Route::get('library/issue', [LibraryController::class, 'issue'])->name('library.issue');
    Route::post('library/issue', [LibraryController::class, 'issueStore'])->name('library.issue.store');
    Route::get('library/books/create', [LibraryController::class, 'create'])->name('library.books.create');
    Route::post('library/books', [LibraryController::class, 'store'])->name('library.books.store');
    Route::get('library/books/{book}', [LibraryController::class, 'show'])->name('library.books.show');
    Route::get('library/books/{book}/edit', [LibraryController::class, 'edit'])->name('library.books.edit');
    Route::put('library/books/{book}', [LibraryController::class, 'update'])->name('library.books.update');

    // ============================================================
    // TRANSPORT
    // ============================================================
    Route::get('transport/vehicles', [TransportController::class, 'vehicles'])->name('transport.vehicles');
    Route::post('transport/vehicles', [TransportController::class, 'storeVehicle'])->name('transport.vehicles.store');
    Route::put('transport/vehicles/{vehicle}', [TransportController::class, 'updateVehicle'])->name('transport.vehicles.update');
    Route::post('transport/drivers', [TransportController::class, 'storeDriver'])->name('transport.drivers.store');
    Route::put('transport/drivers/{driver}', [TransportController::class, 'updateDriver'])->name('transport.drivers.update');
    Route::get('transport', [TransportController::class, 'index'])->name('transport.index');
    Route::post('transport', [TransportController::class, 'store'])->name('transport.store');
    Route::get('transport/{route}', [TransportController::class, 'show'])->name('transport.show');
    Route::post('transport/{route}/stops', [TransportController::class, 'storeStop'])->name('transport.stops.store');
    Route::delete('transport/stops/{stop}', [TransportController::class, 'destroyStop'])->name('transport.stops.destroy');
    Route::post('transport/{route}/assign', [TransportController::class, 'assignStudent'])->name('transport.assign');
    Route::delete('transport/assignments/{assignment}', [TransportController::class, 'removeStudent'])->name('transport.assign.remove');

    // ============================================================
    // INVENTORY
    // ============================================================
    Route::get('inventory/catalogue', [InventoryController::class, 'catalogue'])->name('inventory.catalogue');
    Route::post('inventory/catalogue', [InventoryController::class, 'storeCatalogue'])->name('inventory.catalogue.store');
    Route::get('inventory/assets', [InventoryController::class, 'assets'])->name('inventory.assets');
    Route::post('inventory/assets', [InventoryController::class, 'storeAsset'])->name('inventory.assets.store');
    Route::get('inventory/items/{item}', [InventoryController::class, 'showItem'])->name('inventory.items.show');
    Route::post('inventory/items/{item}/movement', [InventoryController::class, 'recordMovement'])->name('inventory.items.movement');
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/items', [InventoryController::class, 'storeItem'])->name('inventory.items.store');

    // ============================================================
    // ANNOUNCEMENTS
    // ============================================================
    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    Route::patch('announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
    Route::patch('announcements/{announcement}/archive', [AnnouncementController::class, 'archive'])->name('announcements.archive');

    // ============================================================
    // EVENTS
    // ============================================================
    Route::get('events', [EventController::class, 'index'])->name('events.index');
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::patch('events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

    // ============================================================
    // MESSAGES
    // ============================================================
    Route::get('messages/users/search', [MessageController::class, 'searchUsers'])->name('messages.users.search');
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::patch('messages/{message}/star', [MessageController::class, 'star'])->name('messages.star');
    Route::patch('messages/{message}/trash', [MessageController::class, 'trash'])->name('messages.trash');

    // ============================================================
    // PAYROLL
    // ============================================================
    Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
    Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
    Route::get('payroll/{payrollRun}', [PayrollController::class, 'show'])->name('payroll.show');
    Route::patch('payroll/{payrollRun}/approve', [PayrollController::class, 'approve'])->name('payroll.approve');
    Route::patch('payroll/{payrollRun}/mark-paid', [PayrollController::class, 'markPaid'])->name('payroll.mark-paid');
    Route::get('payslips/{payslip}', [PayrollController::class, 'payslip'])->name('payroll.payslip');
    Route::put('payslips/{payslip}', [PayrollController::class, 'updatePayslip'])->name('payroll.payslip.update');
    Route::get('payslips/{payslip}/pdf', [PayrollController::class, 'payslipPdf'])->name('payroll.payslip.pdf');

    // ============================================================
    // BULK SMS ALERTS
    // ============================================================
    Route::get('sms', [SmsAlertController::class, 'index'])->name('sms.index');
    Route::get('sms/create', [SmsAlertController::class, 'create'])->name('sms.create');
    Route::post('sms', [SmsAlertController::class, 'store'])->name('sms.store');
    Route::get('sms/{smsAlert}', [SmsAlertController::class, 'show'])->name('sms.show');

    // ============================================================
    // AUDIT LOG
    // ============================================================
    // Reports & Analytics
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('reports/defaulters', [ReportController::class, 'defaulters'])->name('reports.defaulters');
    Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('reports/enrollment', [ReportController::class, 'enrollment'])->name('reports.enrollment');

    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/{audit}', [AuditController::class, 'show'])->name('audit.show');
});
