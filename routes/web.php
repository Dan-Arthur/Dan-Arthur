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
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\TransportController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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
    Route::put('results/{result}/comment', [ResultController::class, 'updateComment'])->name('results.comment');
    Route::patch('results/{result}/approve', [ResultController::class, 'approve'])->name('results.approve');
    Route::patch('results/{result}/publish', [ResultController::class, 'publish'])->name('results.publish');

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

    // ============================================================
    // FINANCE: PAYMENTS
    // ============================================================
    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::patch('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');

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
    Route::post('transport/drivers', [TransportController::class, 'storeDriver'])->name('transport.drivers.store');
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
    // AUDIT LOG
    // ============================================================
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/{audit}', [AuditController::class, 'show'])->name('audit.show');
});
