<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Staff\EventController;
use App\Http\Controllers\Staff\ParticipantController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\Staff\QrScanController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\Admin\ReportFileController;
use App\Models\Designation;
use App\Models\Staff;

// Password change routes for all roles
Route::post('/student/change-password', [App\Http\Controllers\Student\ProfileController::class, 'changePassword'])->name('student.change-password')->middleware(['auth']);
Route::post('/staff/change-password', [App\Http\Controllers\Staff\ProfileController::class, 'changePassword'])->name('staff.change-password')->middleware(['auth']);
Route::post('/student-leader/change-password', [App\Http\Controllers\Assistant\ProfileController::class, 'changePassword'])->name('student-leader.change-password')->middleware(['auth']);
Route::post('/admin/change-password', [App\Http\Controllers\Admin\ProfileController::class, 'changePassword'])->name('admin.change-password')->middleware(['auth']);

// About Me update routes for all roles
Route::post('/staff/update-about-me', [App\Http\Controllers\Admin\StaffController::class, 'updateAboutMe'])->name('staff.update-about-me')->middleware(['auth']);
Route::post('/student/update-about-me', [App\Http\Controllers\Student\ProfileController::class, 'updateAboutMe'])->name('student.update-about-me')->middleware(['auth']);
Route::post('/student-leader/update-about-me', [App\Http\Controllers\Assistant\ProfileController::class, 'updateAboutMe'])->name('student-leader.update-about-me')->middleware(['auth']);
Route::post('/admin/update-about-me', [App\Http\Controllers\Admin\ProfileController::class, 'updateAboutMe'])->name('admin.update-about-me')->middleware(['auth']);

// Password reset routes
Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

// Debug route - only available in non-production environments and for admins
Route::get('/debug/user-info', function() {
    // Only allow in non-production environments
    if (app()->environment('production')) {
        abort(404);
    }
    
    // Require authentication and admin role
    $user = auth()->user();
    if (!$user) {
        return response()->json(['error' => 'Not authenticated'], 401);
    }
    
    // Only admins can access
    if ((int)($user->role ?? 0) !== 4) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $staff = \App\Models\Staff::where('email', $user->email)->first();
    return response()->json([
        'id' => $user->id,
        'email' => $user->email,
        'role' => $user->role,
        'designation' => $user->designation ?? null,
        'staff' => $staff ? [
            'id' => $staff->id,
            'designation' => $staff->designation,
            'employment_status' => $staff->employment_status
        ] : null
    ]);
})->middleware(['auth', 'role:4']);
// View report file (JSON as HTML, XLSX as download)
Route::get('/admin/staff/dashboard/AdmissionServicesOfficer/report/view/{filename}', [ReportFileController::class, 'view'])
    ->where('filename', '.*')
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.report.view');

// Delete report file
Route::post('/admin/staff/dashboard/AdmissionServicesOfficer/report/delete/{filename}', [ReportFileController::class, 'delete'])
    ->where('filename', '.*')
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.report.delete');


// Route for /admin/staff/dashboard/{designation}/report
Route::get('/admin/staff/dashboard/{designation}/report', function($designation) {
    $user = auth()->user();
    $userDesignation = $user?->designation ?? '';
    $profileDesignation = $user?->staffProfile?->designation ?? '';
    $isAdmissionOfficer = (
        strcasecmp($designation, 'Admission Services Officer') === 0 ||
        strcasecmp($userDesignation, 'Admission Services Officer') === 0 ||
        strcasecmp($profileDesignation, 'Admission Services Officer') === 0
    );
    $isAdmin = (int)($user?->role ?? 0) === 4;
    if (!($isAdmissionOfficer || $isAdmin)) {
        abort(403, 'Unauthorized access.');
    }
    return view('admin.staff.report');
})->middleware(['auth', 'verified'])->name('admin.staff.dashboard.report');

// Event creation for all staff - accessible to all authenticated staff and admins
Route::get('/admin/staff/dashboard/StudentOrgModerator/create-event', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.create-event');
Route::get('/admin/staff/dashboard/StudentOrgModerator/view-events', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.view-events');
Route::get('/admin/staff/dashboard/StudentOrgModerator', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'index'])
    ->name('admin.staff.dashboard.StudentOrgModerator');

// Create event (POST)
Route::post('/admin/staff/dashboard/StudentOrgModerator/event', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.event.store');

// Guidance Counselor event management (American spelling)
Route::get('/admin/staff/dashboard/GuidanceCounselor/create-event', [\App\Http\Controllers\Admin\GuidanceCounselorEventController::class, 'create'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.GuidanceCounselor.create-event');
Route::post('/admin/staff/dashboard/GuidanceCounselor/event', [\App\Http\Controllers\Admin\GuidanceCounselorEventController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.GuidanceCounselor.event.store');
Route::get('/admin/staff/dashboard/GuidanceCounselor/pending-events', [\App\Http\Controllers\Admin\GuidanceCounselorEventController::class, 'pendingEvents'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.GuidanceCounselor.pending-events');
Route::get('/admin/staff/dashboard/GuidanceCounselor/approved-events', [\App\Http\Controllers\Admin\GuidanceCounselorEventController::class, 'approvedEvents'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.GuidanceCounselor.approved-events');
Route::get('/admin/staff/dashboard/GuidanceCounselor/events-history', [\App\Http\Controllers\Admin\GuidanceCounselorEventController::class, 'eventsHistory'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.GuidanceCounselor.events-history');

// Guidance Counselor service pages
Route::get('/admin/staff/dashboard/{designation}/service/{service}', [\App\Http\Controllers\Admin\StaffDashboardController::class, 'showGuidanceCounselorService'])
    ->middleware(['auth', 'verified'])
    ->where('designation', '.*')
    ->where('service', '.*')
    ->name('admin.staff.dashboard.guidance-counselor.service');

// Guidance Counselor clients list
Route::get('/admin/staff/dashboard/{designation}/clients', [\App\Http\Controllers\Admin\StaffDashboardController::class, 'showGuidanceCounselorClients'])
    ->middleware(['auth', 'verified'])
    ->where('designation', '.*')
    ->name('admin.staff.dashboard.guidance-counselor.clients');
// Edit event
Route::get('/admin/staff/dashboard/StudentOrgModerator/event/{event}/edit', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'edit'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.event.edit');
// Update event
Route::put('/admin/staff/dashboard/StudentOrgModerator/event/{event}', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.event.update');
// Delete event
Route::delete('/admin/staff/dashboard/StudentOrgModerator/event/{event}', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'destroy'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.event.delete');
// QR code for event
Route::get('/admin/staff/dashboard/StudentOrgModerator/event/{event}/qrcode', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'qrcode'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.event.qrcode');
// Event details (GET)
Route::get('/admin/staff/dashboard/StudentOrgModerator/event/{event}', [\App\Http\Controllers\Admin\StudentOrgModeratorEventController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard.StudentOrgModerator.event.show');

// Add POST route for student-management (add student form)
// Add GET route for student-management (view page)
Route::get('/admin/staff/dashboard/AdmissionServicesOfficer/student-management', [\App\Http\Controllers\Admin\StudentController::class, 'index'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student-management');
Route::post('/admin/staff/dashboard/AdmissionServicesOfficer/student-management', [\App\Http\Controllers\Admin\StudentController::class, 'store'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student-management.store');
Route::get('/admin/staff/dashboard/AdmissionServicesOfficer/student/{id}', [\App\Http\Controllers\Admin\StudentController::class, 'show'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student.show');
Route::get('/admin/staff/dashboard/AdmissionServicesOfficer/student/{student}/edit', [\App\Http\Controllers\Admin\StudentController::class, 'edit'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student.edit');
Route::put('/admin/staff/dashboard/AdmissionServicesOfficer/student/{student}', [\App\Http\Controllers\Admin\StudentController::class, 'update'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student.update');
Route::post('/admin/staff/dashboard/AdmissionServicesOfficer/student/{student}/resend-verification', [\App\Http\Controllers\Admin\StudentController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student.resend-verification');
Route::delete('/admin/staff/dashboard/AdmissionServicesOfficer/student/{student}', [\App\Http\Controllers\Admin\StudentController::class, 'destroy'])
    ->middleware(['auth', 'verified', \App\Http\Middleware\CheckStaffDesignation::class . ':Admission Services Officer'])
    ->name('admin.staff.dashboard.AdmissionServicesOfficer.student.destroy');

// Always-available report route for sidebar
Route::get('/sidebar/report', function() {
    $user = auth()->user();
    $designation = $user?->designation ?? '';
    $profileDesignation = $user?->staffProfile?->designation ?? '';
    $isAdmissionOfficer = strcasecmp($designation, 'Admission Services Officer') === 0 || strcasecmp($profileDesignation, 'Admission Services Officer') === 0;
    $isAdmin = (int)($user?->role ?? 0) === 4;
    if (!($isAdmissionOfficer || $isAdmin)) {
        abort(403, 'Unauthorized access.');
    }
    return view('admin.staff.report');
})->middleware(['auth', 'verified'])->name('report');
Route::get('/admin/staff/dashboard/AdmissionServicesOfficer/report', function() {
    $user = auth()->user();
    $designation = $user?->designation ?? '';
    $profileDesignation = $user?->staffProfile?->designation ?? '';
    $isAdmissionOfficer = strcasecmp($designation, 'Admission Services Officer') === 0 || strcasecmp($profileDesignation, 'Admission Services Officer') === 0;
    $isAdmin = (int)($user?->role ?? 0) === 4;
    if (!($isAdmissionOfficer || $isAdmin)) {
        abort(403, 'Unauthorized access.');
    }
    return view('admin.staff.report');
})->middleware(['auth', 'verified'])->name('admin.staff.dashboard.AdmissionServicesOfficer.report');

// Staff profile route
Route::get('/staff/profile', [App\Http\Controllers\Admin\StaffController::class, 'profile'])->name('staff.profile');

// Nationality management routes (Admin only)
Route::resource('admin/nationalities', App\Http\Controllers\Admin\NationalityController::class)
    ->middleware(['auth', 'verified', 'role:4'])
    ->names([
        'index' => 'admin.nationalities.index',
        'create' => 'admin.nationalities.create',
        'store' => 'admin.nationalities.store',
        'show' => 'admin.nationalities.show',
        'edit' => 'admin.nationalities.edit',
        'update' => 'admin.nationalities.update',
        'destroy' => 'admin.nationalities.destroy',
    ]);

// Address Management Routes (Admin only)
Route::middleware(['auth', 'verified', 'role:4'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/addresses', [\App\Http\Controllers\Admin\AddressManagementController::class, 'index'])->name('addresses.index');
    
    // Provinces
    Route::get('/addresses/provinces', [\App\Http\Controllers\Admin\AddressManagementController::class, 'provinces'])->name('addresses.provinces');
    Route::post('/addresses/provinces', [\App\Http\Controllers\Admin\AddressManagementController::class, 'storeProvince'])->name('addresses.provinces.store');
    Route::put('/addresses/provinces/{province}', [\App\Http\Controllers\Admin\AddressManagementController::class, 'updateProvince'])->name('addresses.provinces.update');
    Route::delete('/addresses/provinces/{province}', [\App\Http\Controllers\Admin\AddressManagementController::class, 'destroyProvince'])->name('addresses.provinces.destroy');
    
    // Cities
    Route::get('/addresses/provinces/{province}/cities', [\App\Http\Controllers\Admin\AddressManagementController::class, 'cities'])->name('addresses.cities');
    Route::post('/addresses/provinces/{province}/cities', [\App\Http\Controllers\Admin\AddressManagementController::class, 'storeCity'])->name('addresses.cities.store');
    Route::put('/addresses/provinces/{province}/cities/{city}', [\App\Http\Controllers\Admin\AddressManagementController::class, 'updateCity'])->name('addresses.cities.update');
    Route::delete('/addresses/provinces/{province}/cities/{city}', [\App\Http\Controllers\Admin\AddressManagementController::class, 'destroyCity'])->name('addresses.cities.destroy');
    
    // Barangays
    Route::get('/addresses/provinces/{province}/cities/{city}/barangays', [\App\Http\Controllers\Admin\AddressManagementController::class, 'barangays'])->name('addresses.barangays');
    Route::post('/addresses/provinces/{province}/cities/{city}/barangays', [\App\Http\Controllers\Admin\AddressManagementController::class, 'storeBarangay'])->name('addresses.barangays.store');
    Route::put('/addresses/provinces/{province}/cities/{city}/barangays/{barangay}', [\App\Http\Controllers\Admin\AddressManagementController::class, 'updateBarangay'])->name('addresses.barangays.update');
    Route::delete('/addresses/provinces/{province}/cities/{city}/barangays/{barangay}', [\App\Http\Controllers\Admin\AddressManagementController::class, 'destroyBarangay'])->name('addresses.barangays.destroy');
});

// Reports routes - accessible to all authenticated users with role-based filtering
Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('index');
    Route::get('/my-activity', [\App\Http\Controllers\Admin\ReportController::class, 'myActivity'])->name('my-activity');
    Route::get('/appointments', [\App\Http\Controllers\Admin\ReportController::class, 'appointments'])->name('appointments');
    Route::get('/events', [\App\Http\Controllers\Admin\ReportController::class, 'events'])->name('events');
    Route::get('/scholars', [\App\Http\Controllers\Admin\ReportController::class, 'scholars'])->name('scholars');
    
    // Student-specific routes (students and admins)
    Route::get('/students', [\App\Http\Controllers\Admin\ReportController::class, 'students'])
        ->name('students')
        ->middleware('role:1,4');
    
    // Admin-only routes
    Route::middleware('role:4')->group(function () {
        Route::get('/suspensions', [\App\Http\Controllers\Admin\ReportController::class, 'suspensions'])->name('suspensions');
        Route::get('/comprehensive', [\App\Http\Controllers\Admin\ReportController::class, 'comprehensive'])->name('comprehensive');
    });
});

// Admin management routes (Admin only)
Route::get('/admins', [App\Http\Controllers\Admin\AdminManagementController::class, 'index'])->name('admins.index')->middleware(['auth', 'verified', 'role:4']);
Route::get('/admins/create', [App\Http\Controllers\Admin\AdminManagementController::class, 'create'])->name('admins.create')->middleware(['auth', 'verified', 'role:4']);
Route::post('/admins', [App\Http\Controllers\Admin\AdminManagementController::class, 'store'])->name('admins.store')->middleware(['auth', 'verified', 'role:4']);
Route::get('/admins/{id}/edit', [App\Http\Controllers\Admin\AdminManagementController::class, 'edit'])->name('admins.edit')->middleware(['auth', 'verified', 'role:4']);
Route::put('/admins/{id}', [App\Http\Controllers\Admin\AdminManagementController::class, 'update'])->name('admins.update')->middleware(['auth', 'verified', 'role:4']);
Route::delete('/admins/{id}', [App\Http\Controllers\Admin\AdminManagementController::class, 'destroy'])->name('admins.destroy')->middleware(['auth', 'verified', 'role:4']);

// Designation management routes (Admin only)
Route::resource('admin/designations', App\Http\Controllers\Admin\DesignationController::class)
    ->middleware(['auth', 'verified', 'role:4'])
    ->names([
        'index' => 'admin.designations.index',
        'create' => 'admin.designations.create',
        'store' => 'admin.designations.store',
        'edit' => 'admin.designations.edit',
        'update' => 'admin.designations.update',
        'destroy' => 'admin.designations.destroy',
    ]);

// Department management routes (Admin only)
Route::resource('admin/departments', App\Http\Controllers\Admin\DepartmentController::class)
    ->middleware(['auth', 'verified', 'role:4'])
    ->names([
        'index' => 'admin.departments.index',
        'create' => 'admin.departments.create',
        'store' => 'admin.departments.store',
        'edit' => 'admin.departments.edit',
        'update' => 'admin.departments.update',
        'destroy' => 'admin.departments.destroy',
    ]);

// Course and section management within departments
Route::middleware(['auth', 'verified', 'role:4'])->prefix('admin/departments')->name('admin.departments.')->group(function () {
    Route::post('/{department}/courses', [App\Http\Controllers\Admin\DepartmentController::class, 'storeCourse'])->name('courses.store');
    Route::put('/{department}/courses/{course}', [App\Http\Controllers\Admin\DepartmentController::class, 'updateCourse'])->name('courses.update');
    Route::delete('/{department}/courses/{course}', [App\Http\Controllers\Admin\DepartmentController::class, 'destroyCourse'])->name('courses.destroy');
});

// Course management routes (Admin only)
Route::resource('admin/courses', App\Http\Controllers\Admin\CourseController::class)
    ->middleware(['auth', 'verified', 'role:4'])
    ->names([
        'index' => 'admin.courses.index',
        'create' => 'admin.courses.create',
        'store' => 'admin.courses.store',
        'edit' => 'admin.courses.edit',
        'update' => 'admin.courses.update',
        'destroy' => 'admin.courses.destroy',
    ]);

// Organization management routes (Admin only)
Route::resource('admin/organizations', App\Http\Controllers\Admin\OrganizationController::class)
    ->middleware(['auth', 'verified', 'role:4'])
    ->names([
        'index' => 'admin.organizations.index',
        'create' => 'admin.organizations.create',
        'store' => 'admin.organizations.store',
        'edit' => 'admin.organizations.edit',
        'update' => 'admin.organizations.update',
        'destroy' => 'admin.organizations.destroy',
    ]);

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 🏠 Public welcome page (for guests only)
Route::get('/', function () {
    // Fetch staff from staff_information table with images, ordered by designation and name
    $staff = \App\Models\Staff::with(['department', 'organization', 'organizations'])
        ->whereNotNull('image')
        ->where('image', '!=', '')
        ->orderBy('designation')
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();
    $monthParam = request('month'); // format YYYY-MM
    $base = $monthParam ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth() : now()->startOfMonth();
    $monthStart = $base->copy()->startOfMonth();
    $monthEnd = $base->copy()->endOfMonth();
    
    // Get all approved upcoming events (not just current month)
    $allApprovedEvents = \App\Models\Event::where('status', 'approved')
        ->where('event_date', '>=', now())
        ->with('creator')
        ->get(['id','name','description','event_date','end_date','start_time','end_time','location','created_by']);
    
    // Separate staff events (created by role=2) and admin events (created by role=4)
    $staffUserIds = \App\Models\User::where('role', 2)->pluck('id')->toArray();
    $adminUserIds = \App\Models\User::where('role', 4)->pluck('id')->toArray();
    
    // Staff events - sorted by date and time
    // Include staff events AND admin events that are USTP System Imposed Activity or Balubal Campus Activity
    $staffEvents = $allApprovedEvents->filter(function($event) use ($staffUserIds, $adminUserIds) {
        // Include staff events
        if (in_array($event->created_by, $staffUserIds)) {
            return true;
        }
        
        // Include admin events that are USTP System Imposed Activity or Balubal Campus Activity
        if (in_array($event->created_by, $adminUserIds)) {
            $name = strtolower($event->name ?? '');
            $desc = strtolower($event->description ?? '');
            $isUstpActivity = strpos($name, 'ustp system imposed activity') !== false || 
                             strpos($desc, 'ustp system imposed activity') !== false;
            $isBalubalActivity = strpos($name, 'balubal campus activity') !== false || 
                                strpos($desc, 'balubal campus activity') !== false;
            
            return $isUstpActivity || $isBalubalActivity;
        }
        
        return false;
    })->sortBy(function($event) {
        $date = \Carbon\Carbon::parse($event->event_date);
        $time = $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i:s') : '00:00:00';
        return $date->format('Y-m-d') . ' ' . $time;
    })->values();
    
    // Admin events - categorized
    $adminEvents = $allApprovedEvents->filter(function($event) use ($adminUserIds) {
        return in_array($event->created_by, $adminUserIds);
    });
    
    // Categorize admin events
    $semesterDates = $adminEvents->filter(function($event) {
        $name = strtolower($event->name ?? '');
        $desc = strtolower($event->description ?? '');
        return strpos($name, 'semester') !== false || strpos($desc, 'semester') !== false || 
               strpos($name, 'sem') !== false || strpos($desc, 'sem') !== false;
    });
    
    // Holidays: Only admin events with specific descriptions
    $holidays = $adminEvents->filter(function($event) {
        $desc = strtolower($event->description ?? '');
        // Only include if description contains "National Holiday", "City Holidays", or "Barangay Holiday"
        return strpos($desc, 'national holiday') !== false || 
               strpos($desc, 'city holidays') !== false || 
               strpos($desc, 'city holiday') !== false ||
               strpos($desc, 'barangay holiday') !== false;
    });
    
    // School Days: Admin events that are not semester dates or holidays
    // Note: USTP System Imposed Activity and Balubal Campus Activity are included in staffEvents
    $schoolDays = $adminEvents->filter(function($event) use ($semesterDates, $holidays) {
        // Exclude semester dates and holidays
        if ($semesterDates->contains($event) || $holidays->contains($event)) {
            return false;
        }
        
        // Exclude USTP System Imposed Activity and Balubal Campus Activity (they're in staffEvents)
        $name = strtolower($event->name ?? '');
        $desc = strtolower($event->description ?? '');
        $isUstpActivity = strpos($name, 'ustp system imposed activity') !== false || 
                         strpos($desc, 'ustp system imposed activity') !== false;
        $isBalubalActivity = strpos($name, 'balubal campus activity') !== false || 
                            strpos($desc, 'balubal campus activity') !== false;
        
        if ($isUstpActivity || $isBalubalActivity) {
            return false; // These are shown in staffEvents
        }
        
        // Include all other admin events that are not semester dates or holidays
        return true;
    });
    
    // For calendar display (current month only)
    $approvedEvents = $allApprovedEvents->filter(function($event) use ($monthStart, $monthEnd) {
        $eventDate = \Carbon\Carbon::parse($event->event_date);
        return $eventDate->between($monthStart, $monthEnd);
    });
    
    // Calendar data for academic year view
    $calendarYear = request('year', now()->year);
    $calendarMonthParam = request('cal_month'); // Format: "August-2024" or month name
    // Get all approved events (same as admin calendar) - use start_time for consistency
    $calendarEvents = \App\Models\Event::where('status', 'approved')
        ->orderBy('start_time')
        ->get();
    
    // Group events by date for calendar display (use start_time like admin calendar)
    $eventsByDate = $calendarEvents->groupBy(function($event) {
        return \Carbon\Carbon::parse($event->start_time ?? $event->event_date)->format('Y-m-d');
    });
    
    // Determine which month to display (default to current month or August of academic year)
    $months = ['August', 'September', 'October', 'November', 'December', 'January', 
              'February', 'March', 'April', 'May', 'June'];
    $currentMonthIndex = 0; // Default to August
    if ($calendarMonthParam) {
        $parts = explode('-', $calendarMonthParam);
        $monthName = $parts[0] ?? 'August';
        $monthIndex = array_search($monthName, $months);
        if ($monthIndex !== false) {
            $currentMonthIndex = $monthIndex;
        }
    }
    
    $calendarMonth = $monthStart->format('Y-m');
    
    // Get all background images from bg1_image folder
    $bgImages = [];
    $bgImagePath = public_path('assets/img/bg1_image');
    if (is_dir($bgImagePath)) {
        $files = \Illuminate\Support\Facades\File::files($bgImagePath);
        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $bgImages[] = asset('assets/img/bg1_image/' . $file->getFilename());
            }
        }
        // Sort images by filename for consistent order
        sort($bgImages);
    }
    
    return view('welcome', compact('staff', 'approvedEvents', 'calendarMonth', 'staffEvents', 'semesterDates', 'holidays', 'schoolDays', 'calendarYear', 'calendarEvents', 'eventsByDate', 'months', 'currentMonthIndex', 'bgImages'));
})->middleware('guest')->name('welcome');

// Public daily events view (approved only)
Route::get('/events/day', function() {
    $date = request('date');
    abort_if(!$date, 404);
    $day = \Carbon\Carbon::parse($date)->toDateString();
    $events = \App\Models\Event::where('status', 'approved')
        ->whereDate('event_date', $day)
        ->orderBy('start_time')
        ->get(['id','name','description','event_date','end_date','start_time','end_time','location']);
    return view('events.day', compact('events', 'day'));
})->name('events.day');

// 🎯 Role-based redirect after login
Route::get('/home', [RedirectController::class, 'index'])->name('home');
// Authentication routes with rate limiting
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login')->middleware('guest');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1'); // 5 attempts per minute
// Registration disabled: Only Admission Services Officer can add students via dashboard
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout'); 

// Profile image update route (available to all authenticated users)
Route::post('/profile/update-image', [\App\Http\Controllers\ProfileController::class, 'updateImage'])
    ->middleware('auth')
    ->name('profile.update-image');
Route::delete('/profile/delete-image', [\App\Http\Controllers\ProfileController::class, 'deleteImage'])
    ->middleware('auth')
    ->name('profile.delete-image'); 

// 👩‍🎓 Student routes (role: 1)
Route::middleware(['auth', 'verified',])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/calendar', [StudentDashboardController::class, 'calendarView'])->name('dashboard.calendar');
        Route::get('/qr-code', [StudentDashboardController::class, 'qrCode'])->name('qr-code');
        Route::get('/profile', [\App\Http\Controllers\Student\ProfileController::class, 'show'])->name('profile');

        // Route for student.make-appointment view
        Route::get('/make-appointment', [\App\Http\Controllers\AppointmentController::class, 'index'])->name('make-appointment');

        Route::get('/appointments', [\App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
        Route::delete('/appointments/{id}', function ($id) {
            $updated = \App\Models\Appointment::where('id', $id)
                ->where('user_id', auth()->id())
                ->whereIn('status', ['pending','rescheduled'])
                ->update(['status' => 'cancelled']);
            if (! $updated) {
                return back()->with('error', 'Only pending or rescheduled appointments can be cancelled.');
            }
            return back()->with('success', 'Appointment cancelled.');
        })->name('appointments.cancel');

    // Student export of own participations
    // Note: The group has name('student.'), so this becomes 'student.participants.export'
    Route::get('/participants/export', [StudentDashboardController::class, 'exportParticipants'])->name('participants.export');

    // Event feedback routes
    Route::get('/events/feedback', [\App\Http\Controllers\Student\EventFeedbackController::class, 'index'])->name('events.feedback.index');
    Route::get('/events/{eventId}/feedback', [\App\Http\Controllers\Student\EventFeedbackController::class, 'create'])->name('events.feedback.create');
    Route::post('/events/{eventId}/feedback', [\App\Http\Controllers\Student\EventFeedbackController::class, 'store'])->name('events.feedback.store');

            // Reschedule approved appointment
            Route::put('/appointments/{id}/reschedule', [\App\Http\Controllers\Student\AppointmentController::class, 'reschedule'])->name('appointments.reschedule');

            // Event filtering
            Route::get('/events', [StudentDashboardController::class, 'events'])->name('events.index');
    });

// 👨‍🏫 Staff routes (role: 2)
Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class.':2'])
    ->prefix('staff')
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');


        // Staff appointments index
        Route::get('/appointments', [AppointmentController::class, 'staffIndex'])->name('appointments.index');

    // Staff events index
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    // Staff event creation - available to all staff
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    // Staff event approval routes
    Route::post('/events/{id}/approve', [EventController::class, 'approve'])->name('events.approve');
    Route::post('/events/{id}/decline', [EventController::class, 'decline'])->name('events.decline');

        // Appointment approval routes (authorized)
        Route::post('/appointments/{id}/approve', [AppointmentController::class, 'approve'])->name('appointments.approve');
        Route::post('/appointments/{id}/decline', [AppointmentController::class, 'decline'])->name('appointments.decline');

        // Participants route moved outside this group so custom guard can handle non-staff redirects

        // QR Scan
        Route::get('/qrscan', [QrScanController::class, 'index'])->name('qrscan');
        Route::post('/scan-qr', [QrScanController::class, 'scan'])->name('scan-qr');

        // Staff community chatroom (announcements & inquiries)
        Route::get('/community', [\App\Http\Controllers\Staff\CommunityController::class, 'index'])->name('community.index');
        Route::post('/community', [\App\Http\Controllers\Staff\CommunityController::class, 'store'])->name('community.store');

        // My Organizations - for all staff handling organizations
        Route::get('/organizations', [\App\Http\Controllers\Staff\AssistantController::class, 'organizations'])->name('organizations.index');
        Route::get('/organizations/{organizationId}/assistants', [\App\Http\Controllers\Staff\AssistantController::class, 'organizationAssistants'])->name('organizations.assistants');
        Route::get('/organizations/{organizationId}/student-leaders', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'organizationStudentLeaders'])->name('organizations.student-leaders');
        Route::put('/organizations/{id}', [\App\Http\Controllers\Admin\DashboardController::class, 'updateOrganization'])->name('organizations.update');
        
        // Staff File Management - for all staff to store organization files including Personal Data Sheets
        Route::get('/files', [\App\Http\Controllers\Staff\FileController::class, 'index'])->name('files.index');
        Route::get('/files/create', [\App\Http\Controllers\Staff\FileController::class, 'create'])->name('files.create');
        Route::post('/files', [\App\Http\Controllers\Staff\FileController::class, 'store'])->name('files.store');
        Route::get('/files/{id}/download', [\App\Http\Controllers\Staff\FileController::class, 'download'])->name('files.download');
        Route::delete('/files/{id}', [\App\Http\Controllers\Staff\FileController::class, 'destroy'])->name('files.destroy');
        
        // Organization Files - for all staff handling organizations (legacy)
        Route::get('/organizations/{organizationId}/files', [\App\Http\Controllers\Staff\OrganizationFileController::class, 'index'])->name('organization-files.index');
        
        // Staff Organization Files - personal file folders for staff
        Route::get('/organizations/{organizationId}/my-files', [\App\Http\Controllers\Staff\StaffOrganizationFileController::class, 'index'])->name('staff.organization-files.index');
        Route::get('/organizations/{organizationId}/my-files/create', [\App\Http\Controllers\Staff\StaffOrganizationFileController::class, 'create'])->name('staff.organization-files.create');
        Route::post('/organizations/{organizationId}/my-files', [\App\Http\Controllers\Staff\StaffOrganizationFileController::class, 'store'])->name('staff.organization-files.store');
        Route::get('/organizations/{organizationId}/my-files/{fileId}/download', [\App\Http\Controllers\Staff\StaffOrganizationFileController::class, 'download'])->name('staff.organization-files.download');
        Route::delete('/organizations/{organizationId}/my-files/{fileId}', [\App\Http\Controllers\Staff\StaffOrganizationFileController::class, 'destroy'])->name('staff.organization-files.destroy');
        Route::get('/organizations/{organizationId}/files/create', [\App\Http\Controllers\Staff\OrganizationFileController::class, 'create'])->name('organization-files.create');
        Route::post('/organizations/{organizationId}/files', [\App\Http\Controllers\Staff\OrganizationFileController::class, 'store'])->name('organization-files.store');
        Route::get('/organizations/{organizationId}/files/{fileId}/download', [\App\Http\Controllers\Staff\OrganizationFileController::class, 'download'])->name('organization-files.download');
        Route::delete('/organizations/{organizationId}/files/{fileId}', [\App\Http\Controllers\Staff\OrganizationFileController::class, 'destroy'])->name('organization-files.destroy');
        
        // Student leaders management (staff-owned) - available to all staff with organizations
        Route::get('/student-leaders', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'index'])->name('student-leaders.index');
        Route::get('/student-leaders/create', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'create'])->name('student-leaders.create');
        Route::post('/student-leaders', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'store'])->name('student-leaders.store');
        Route::get('/student-leaders/{id}/edit', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'edit'])->name('student-leaders.edit');
        Route::put('/student-leaders/{id}', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'update'])->name('student-leaders.update');
        Route::delete('/student-leaders/{id}', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'destroy'])->name('student-leaders.destroy');
        // Suspend and resume student leader
        Route::put('/student-leaders/{id}/suspend', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'suspend'])->name('student-leaders.suspend');
        Route::put('/student-leaders/{id}/resume', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'resume'])->name('student-leaders.resume');
        // Fetch past organizations for leadership background
        Route::post('/student-leaders/fetch-past-organizations', [\App\Http\Controllers\Staff\StudentLeaderController::class, 'fetchPastOrganizations'])->name('student-leaders.fetch-past-organizations');
    });

// 🧑‍💼 Student leader routes (role: 3) and students with assignments (role: 1)
Route::middleware(['auth', 'verified', \App\Http\Middleware\AssistantAccess::class])
    ->prefix('student-leader')
    ->name('student-leader.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Assistant\DashboardController::class, 'index'])->name('dashboard');
        // Profile
        Route::get('/profile', [\App\Http\Controllers\Assistant\ProfileController::class, 'show'])->name('profile');

        // Events
        Route::get('/events', [\App\Http\Controllers\Assistant\EventController::class, 'index'])->name('events.index');
        Route::get('/events/created', [\App\Http\Controllers\Assistant\EventController::class, 'created'])->name('events.created');
    // Event creation (form and store)
    Route::get('/events/create', [\App\Http\Controllers\Assistant\EventController::class, 'create'])->name('events.create');
    Route::post('/events', [\App\Http\Controllers\Assistant\EventController::class, 'store'])->name('events.store');

        // Required files for events
        Route::get('/events/{id}/requirements', [\App\Http\Controllers\Assistant\EventController::class, 'requirements'])->name('events.requirements');

        // File download/upload from staff
        Route::get('/events/{id}/files', [\App\Http\Controllers\Assistant\FileController::class, 'index'])->name('events.files');
        Route::post('/events/{id}/files/upload', [\App\Http\Controllers\Assistant\FileController::class, 'upload'])->name('events.files.upload');
        Route::get('/events/{id}/files/{file}', [\App\Http\Controllers\Assistant\FileController::class, 'download'])->name('events.files.download');

        // Calendar view (read-only)
        Route::get('/calendar', [\App\Http\Controllers\Assistant\EventController::class, 'calendar'])->name('calendar');

        // Event participants history (department/courses)
        Route::get('/participants/history', [\App\Http\Controllers\Assistant\ParticipantController::class, 'history'])->name('participants.history');

        // QR code scanning (role 1)
        Route::get('/qrscan', [\App\Http\Controllers\Assistant\QrScanController::class, 'index'])->name('qrscan');
        Route::post('/scan-qr', [\App\Http\Controllers\Assistant\QrScanController::class, 'scan'])->name('scan-qr');

        // In-app messaging system
        Route::get('/messages', [\App\Http\Controllers\Assistant\MessageController::class, 'index'])->name('messages.index');
        Route::post('/messages/send', [\App\Http\Controllers\Assistant\MessageController::class, 'send'])->name('messages.send');
    });
    
// Organization update route accessible to both staff and assistants
Route::middleware(['auth', 'verified'])
    ->group(function () {
        Route::put('/staff/organizations/{id}', [\App\Http\Controllers\Admin\DashboardController::class, 'updateOrganization'])->name('staff.organizations.update');
    });

// Allow role 1 students who also have a role 3 account to jump to student leader dashboard with password prompt
Route::middleware(['auth', 'verified'])
    ->post('/student/switch-to-student-leader', function () {
        $user = auth()->user();
        // Verify password
        request()->validate(['student_leader_password' => 'required|string']);
        if (!\Illuminate\Support\Facades\Hash::check(request('student_leader_password'), $user->password)) {
            return back()->with('error', 'Password incorrect for student leader switch.');
        }
        // Ensure they have student leader access via middleware criteria
        if (($user->role === 3) || (($user->role === 1) && $user->assistantAssignments()->where('active', true)->exists())) {
            return redirect()->route('student-leader.dashboard');
        }
        return back()->with('error', 'No student leader access found.');
    })->name('student.switch-to-student-leader');

// Allow role 1 students with assistant access to jump to student leader dashboard with password prompt
Route::middleware(['auth', 'verified'])
    ->post('/student/switch-to-assistant', function () {
        $user = auth()->user();
        // Verify password
        request()->validate(['assistant_password' => 'required|string']);
        if (!\Illuminate\Support\Facades\Hash::check(request('assistant_password'), $user->password)) {
            return back()->with('error', 'Password incorrect for assistant switch.');
        }
        // Ensure they have assistant access
        if (($user->role === 3) || (($user->role === 1) && $user->hasAssistantAccess())) {
            return redirect()->route('student-leader.dashboard');
        }
        return back()->with('error', 'No assistant access found.');
    })->name('student.switch-to-assistant');

// 🧑‍💻 Admin routes (role: 4)
Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class.':4'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/show-student-leaders', [AdminDashboardController::class, 'showStudentLeaders'])->name('show-student-leaders');
        Route::get('/show-students-list', [AdminDashboardController::class, 'showStudentsList'])->name('show-students-list');
        Route::get('/show-admins', [AdminDashboardController::class, 'showAdmins'])->name('show-admins');
        Route::get('/profile', [AdminDashboardController::class, 'profile'])->name('profile');
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/organizations', [AdminDashboardController::class, 'organizations'])->name('organizations.index');
        Route::put('/organizations/{id}/email', [AdminDashboardController::class, 'updateOrganizationEmail'])->name('organizations.update-email');

        // Event management
        Route::post('/events/{id}/approve', [AdminDashboardController::class, 'approveEvent'])->name('events.approve');
        Route::post('/events/{id}/decline', [AdminDashboardController::class, 'declineEvent'])->name('events.decline');
    Route::get('/events', [AdminDashboardController::class, 'events'])->name('events.index');
    Route::get('/events/pending', [AdminDashboardController::class, 'pendingEvents'])->name('events.pending');
    Route::get('/events/current', [AdminDashboardController::class, 'currentEvents'])->name('events.current');
    Route::get('/events/upcoming', [AdminDashboardController::class, 'upcomingEvents'])->name('events.upcoming');
    Route::get('/events/recent', [AdminDashboardController::class, 'recentEvents'])->name('events.recent');
    Route::get('/events/created', [AdminDashboardController::class, 'createdEvents'])->name('events.created');
    // Admin direct-create approved event
    Route::get('/events/create', [AdminDashboardController::class, 'createEvent'])->name('events.create');
    Route::post('/events', [AdminDashboardController::class, 'storeEvent'])->name('events.store');
    Route::post('/events/resolve-duplicate', [AdminDashboardController::class, 'resolveDuplicate'])->name('events.resolve-duplicate');
    Route::post('/events/{id}/requirement', [AdminDashboardController::class, 'addRequirement'])->name('events.add-requirement');
    Route::post('/events/{id}/notify-requirements', [AdminDashboardController::class, 'notifyOrganizationRequirements'])->name('events.notify-requirements');
    Route::get('/events/{id}/edit', [AdminDashboardController::class, 'editEvent'])->name('events.edit');
    Route::get('/events/{id}', [AdminDashboardController::class, 'showEvent'])->name('events.show');
    Route::put('/events/{id}', [AdminDashboardController::class, 'updateEvent'])->name('events.update');
    Route::post('/events/{id}/start-monitoring', [AdminDashboardController::class, 'startParticipationMonitoring'])->name('events.start-monitoring');
    Route::post('/events/{id}/update-thresholds', [AdminDashboardController::class, 'updateMonitoringThresholds'])->name('events.update-thresholds');
    Route::delete('/events/{id}', [AdminDashboardController::class, 'destroyEvent'])->name('events.destroy');
    Route::post('/requirements/bulk-upload', [AdminDashboardController::class, 'bulkUploadRequirements'])->name('requirements.bulkUpload');
    Route::get('/requirements/bulk-download', [AdminDashboardController::class, 'bulkDownloadRequirements'])->name('requirements.bulkDownload');
    Route::get('/events/history', [AdminDashboardController::class, 'eventsHistory'])->name('events.history');
    Route::get('/calendar', [AdminDashboardController::class, 'calendar'])->name('calendar');

        // Staff management (role 4 only - duplicate routes removed, see role 2,4 group below for staff routes)

    // Student leaders management (view + actions)
    Route::get('/student-leaders', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'index'])->name('student-leaders.index');
    Route::get('/student-leaders/create', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'create'])->name('student-leaders.create');
    Route::post('/student-leaders', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'store'])->name('student-leaders.store');
    Route::get('/student-leaders/{id}/edit', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'edit'])->name('student-leaders.edit');
    Route::put('/student-leaders/{id}', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'update'])->name('student-leaders.update');
    Route::delete('/student-leaders/{id}', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'destroy'])->name('student-leaders.destroy');
    Route::put('/student-leaders/{id}/suspend', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'suspend'])->name('student-leaders.suspend');
    Route::put('/student-leaders/{id}/resume', [\App\Http\Controllers\Admin\StudentLeaderController::class, 'resume'])->name('student-leaders.resume');

        // (moved) Staff dashboards by designation declared below for roles 2 & 4

    // Messaging between admin and staff
    Route::get('/messages', [\App\Http\Controllers\Admin\MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/send', [\App\Http\Controllers\Admin\MessageController::class, 'send'])->name('messages.send');

    Route::post('/staff/{id}/update-employee-id', [StaffController::class, 'updateEmployeeId'])->name('staff.updateEmployeeId');
    Route::get('/show-staff', [AdminDashboardController::class, 'showStaff'])->name('show-staff');
        // Export participants
        Route::get('/participants/export', [ParticipantController::class, 'export'])->name('participants.export');

        // Event requirement approval (authorized)
        Route::post('/requirement/{id}/approve', [\App\Http\Controllers\Admin\EventRequirementController::class, 'approve'])->name('requirement.approve');
        
        // QR Code Scanner for Event Participation
        Route::get('/qrscan', [\App\Http\Controllers\Admin\AdminQrScanController::class, 'index'])->name('qrscan');
        Route::post('/qr/scan', [\App\Http\Controllers\Admin\AdminQrScanController::class, 'scan'])->name('qr.scan');
        
        // Organizational Structure Configuration
        Route::post('/org-structure/config', [AdminDashboardController::class, 'updateOrgStructureConfig'])->name('org-structure.config');
        
        // Organizational Structure page
        Route::get('/organizational-structure', [AdminDashboardController::class, 'organizationalStructure'])->name('organizational-structure');
        
        // Files management
        Route::get('/files', [AdminDashboardController::class, 'filesIndex'])->name('files.index');
        Route::get('/files/organization/{fileId}/download', [AdminDashboardController::class, 'downloadOrganizationFile'])->name('files.organization.download');
        Route::get('/files/staff/{fileId}/download', [AdminDashboardController::class, 'downloadStaffFile'])->name('files.staff.download');
        Route::post('/files/admin/upload', [AdminDashboardController::class, 'uploadAdminFile'])->name('files.admin.upload');
        Route::get('/files/admin/{fileId}/download', [AdminDashboardController::class, 'downloadAdminFile'])->name('files.admin.download');
        Route::delete('/files/admin/{fileId}', [AdminDashboardController::class, 'deleteAdminFile'])->name('files.admin.delete');
        Route::post('/files/admin/{fileId}/approve', [AdminDashboardController::class, 'approveAdminFile'])->name('files.admin.approve');
        Route::delete('/files/organization/{fileId}', [AdminDashboardController::class, 'deleteOrganizationFile'])->name('files.organization.delete');
        Route::delete('/files/staff/{fileId}', [AdminDashboardController::class, 'deleteStaffFile'])->name('files.staff.delete');
        Route::post('/files/organization/{fileId}/approve', [AdminDashboardController::class, 'approveOrganizationFile'])->name('files.organization.approve');
        Route::post('/files/staff/{fileId}/approve', [AdminDashboardController::class, 'approveStaffFile'])->name('files.staff.approve');
    });

// Appointments accessible to staff (role 2) and admins (role 4) - for OSA Staff access
Route::middleware(['auth', 'verified', \App\Http\Middleware\RoleMiddleware::class.':2,4'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/appointments', [AdminDashboardController::class, 'appointments'])->name('appointments.index');
        Route::get('/showappointment', [AdminDashboardController::class, 'appointments'])->name('showappointment');
        Route::post('/appointments/{id}/approve', [AdminDashboardController::class, 'approveAppointment'])->name('appointments.approve');
        Route::post('/appointments/{id}/decline', [AdminDashboardController::class, 'declineAppointment'])->name('appointments.decline');
        Route::post('/appointments/{id}/cancel', [AdminDashboardController::class, 'cancelAppointment'])->name('appointments.cancel');
        Route::put('/appointments/{id}/reassign', [AdminDashboardController::class, 'reassignAppointment'])->name('appointments.reassign');
        Route::put('/appointments/{id}/reschedule', [AdminDashboardController::class, 'rescheduleAppointment'])->name('appointments.reschedule');
        Route::put('/appointments/{id}/session', [AdminDashboardController::class, 'updateSession'])->name('appointments.update-session');
        Route::put('/appointments/{id}/remarks', [AdminDashboardController::class, 'updateRemarks'])->name('appointments.update-remarks');
        Route::put('/students/{id}/suspend', [AdminDashboardController::class, 'suspendStudent'])->name('students.suspend');
        Route::put('/students/{id}/reactivate', [AdminDashboardController::class, 'reactivateStudent'])->name('students.reactivate');
        
        // Organization profile accessible to both admin and staff
        Route::get('/organizations/{id}/profile', [AdminDashboardController::class, 'organizationProfile'])->name('organizations.profile');
        Route::put('/organizations/{id}/profile', [AdminDashboardController::class, 'updateOrganization'])->name('organizations.profile.update');
        
        // Organization profile file management
        Route::post('/organizations/{id}/profile/file', [\App\Http\Controllers\Admin\OrganizationProfileFileController::class, 'upload'])->name('organizations.profile.file.upload');
        Route::get('/organizations/{id}/profile/file/{fileId}/view', [\App\Http\Controllers\Admin\OrganizationProfileFileController::class, 'view'])->name('organizations.profile.file.view');
        Route::get('/organizations/{id}/profile/file/{fileId}/download', [\App\Http\Controllers\Admin\OrganizationProfileFileController::class, 'download'])->name('organizations.profile.file.download');
        Route::delete('/organizations/{id}/profile/file/{fileId}', [\App\Http\Controllers\Admin\OrganizationProfileFileController::class, 'destroy'])->name('organizations.profile.file.delete');
        
        // Staff management accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/add-staff', function() {
            $departments = \App\Models\Department::all();
            $organizations = \App\Models\Organization::all();
            return view('admin.add-staff', compact('departments', 'organizations'));
        })->name('add-staff');
        Route::post('/staff', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->name('staff.store');
        
        // Events management accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/events', [AdminDashboardController::class, 'events'])->name('events.index');
        Route::get('/events/pending', [AdminDashboardController::class, 'pendingEvents'])->name('events.pending');
        Route::get('/events/current', [AdminDashboardController::class, 'currentEvents'])->name('events.current');
        Route::get('/events/upcoming', [AdminDashboardController::class, 'upcomingEvents'])->name('events.upcoming');
        Route::get('/events/recent', [AdminDashboardController::class, 'recentEvents'])->name('events.recent');
        Route::get('/events/created', [AdminDashboardController::class, 'createdEvents'])->name('events.created');
        Route::get('/events/create', [AdminDashboardController::class, 'createEvent'])->name('events.create');
        Route::post('/events', [AdminDashboardController::class, 'storeEvent'])->name('events.store');
        Route::post('/events/{id}/approve', [AdminDashboardController::class, 'approveEvent'])->name('events.approve');
        Route::post('/events/{id}/decline', [AdminDashboardController::class, 'declineEvent'])->name('events.decline');
        Route::post('/events/resolve-duplicate', [AdminDashboardController::class, 'resolveDuplicate'])->name('events.resolve-duplicate');
        Route::post('/events/{id}/requirement', [AdminDashboardController::class, 'addRequirement'])->name('events.add-requirement');
        Route::post('/events/{id}/notify-requirements', [AdminDashboardController::class, 'notifyOrganizationRequirements'])->name('events.notify-requirements');
        Route::get('/events/{id}/edit', [AdminDashboardController::class, 'editEvent'])->name('events.edit');
        Route::get('/events/{id}', [AdminDashboardController::class, 'showEvent'])->name('events.show');
        Route::put('/events/{id}', [AdminDashboardController::class, 'updateEvent'])->name('events.update');
        Route::delete('/events/{id}', [AdminDashboardController::class, 'destroyEvent'])->name('events.destroy');
        Route::post('/requirements/bulk-upload', [AdminDashboardController::class, 'bulkUploadRequirements'])->name('requirements.bulkUpload');
        Route::get('/requirements/bulk-download', [AdminDashboardController::class, 'bulkDownloadRequirements'])->name('requirements.bulkDownload');
        Route::get('/events/history', [AdminDashboardController::class, 'eventsHistory'])->name('events.history');
        Route::get('/calendar', [AdminDashboardController::class, 'calendar'])->name('calendar');
        
        // Organizations accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/organizations', [AdminDashboardController::class, 'organizations'])->name('organizations.index');
        Route::put('/organizations/{id}/email', [AdminDashboardController::class, 'updateOrganizationEmail'])->name('organizations.update-email');
        Route::put('/organizations/{id}', [AdminDashboardController::class, 'updateOrganization'])->name('organizations.update');
        
        // Show Students accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/show-students-list', [AdminDashboardController::class, 'showStudentsList'])->name('show-students-list');
        
        // Show Staff accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/show-staff', [AdminDashboardController::class, 'showStaff'])->name('show-staff');
        Route::get('/staff/{id}/edit', [\App\Http\Controllers\Admin\StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{id}', [\App\Http\Controllers\Admin\StaffController::class, 'update'])->name('staff.update');
        Route::post('/staff/{id}/resend-verification', [\App\Http\Controllers\Admin\StaffController::class, 'resendVerificationEmail'])->name('staff.resend-verification');
        Route::delete('/staff/{id}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('staff.destroy');
        Route::post('/staff/{id}/update-employee-id', [\App\Http\Controllers\Admin\StaffController::class, 'updateEmployeeId'])->name('staff.updateEmployeeId');
        
        // Assistants management accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/assistants', [\App\Http\Controllers\Admin\AssistantController::class, 'index'])->name('assistants.index');
        Route::get('/assistants/create', [\App\Http\Controllers\Admin\AssistantController::class, 'create'])->name('assistants.create');
        Route::post('/assistants', [\App\Http\Controllers\Admin\AssistantController::class, 'store'])->name('assistants.store');
        Route::get('/assistants/{id}/edit', [\App\Http\Controllers\Admin\AssistantController::class, 'edit'])->name('assistants.edit');
        Route::put('/assistants/{id}', [\App\Http\Controllers\Admin\AssistantController::class, 'update'])->name('assistants.update');
        Route::delete('/assistants/{id}', [\App\Http\Controllers\Admin\AssistantController::class, 'destroy'])->name('assistants.destroy');
        Route::put('/assistants/{id}/suspend', [\App\Http\Controllers\Admin\AssistantController::class, 'suspend'])->name('assistants.suspend');
        Route::put('/assistants/{id}/resume', [\App\Http\Controllers\Admin\AssistantController::class, 'resume'])->name('assistants.resume');
        Route::get('/show-student-leaders', [AdminDashboardController::class, 'showStudentLeaders'])->name('show-student-leaders');
        
        // Organizational Structure accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/organizational-structure', [AdminDashboardController::class, 'organizationalStructure'])->name('organizational-structure');
        Route::post('/org-structure/config', [AdminDashboardController::class, 'updateOrgStructureConfig'])->name('org-structure.config');
        
        // Files management accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/files', [AdminDashboardController::class, 'filesIndex'])->name('files.index');
        Route::get('/files/organization/{fileId}/download', [AdminDashboardController::class, 'downloadOrganizationFile'])->name('files.organization.download');
        Route::get('/files/staff/{fileId}/download', [AdminDashboardController::class, 'downloadStaffFile'])->name('files.staff.download');
        Route::post('/files/admin/upload', [AdminDashboardController::class, 'uploadAdminFile'])->name('files.admin.upload');
        Route::get('/files/admin/{fileId}/download', [AdminDashboardController::class, 'downloadAdminFile'])->name('files.admin.download');
        Route::delete('/files/admin/{fileId}', [AdminDashboardController::class, 'deleteAdminFile'])->name('files.admin.delete');
        Route::post('/files/admin/{fileId}/approve', [AdminDashboardController::class, 'approveAdminFile'])->name('files.admin.approve');
        
        // Event requirement approval accessible to OSA Staff (role 2) and admins (role 4)
        Route::post('/requirement/{id}/approve', [\App\Http\Controllers\Admin\EventRequirementController::class, 'approve'])->name('requirement.approve');
        
        // QR Code Scanner accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/qrscan', [\App\Http\Controllers\Admin\AdminQrScanController::class, 'index'])->name('qrscan');
        Route::post('/qr/scan', [\App\Http\Controllers\Admin\AdminQrScanController::class, 'scan'])->name('qr.scan');
        
        // Participants export accessible to OSA Staff (role 2) and admins (role 4)
        Route::get('/participants/export', [\App\Http\Controllers\ParticipantController::class, 'export'])->name('participants.export');
    });

// Participants index (staff-only via custom guard; others redirected to own dashboard with message)
Route::middleware(['auth', 'verified', 'staff.participants.guard'])
    ->get('/staff/participants', [ParticipantController::class, 'index'])
    ->name('staff.participants.index');

// 📅 Public appointment booking (open to everyone) with rate limiting
Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store')->middleware('throttle:10,1'); // 10 requests per minute
Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
// Backward-compatible alias for showappointment
Route::get('/showappointment', function () {
    return redirect()->route('appointments.index');
});

// 🧩 Public AJAX endpoints
Route::get('/api/departments', fn() => \App\Models\Department::all());
Route::get('/api/courses/{departmentId}', fn($id) => \App\Models\Course::where('department_id', $id)->get());
Route::get('/api/provinces', [\App\Http\Controllers\AddressController::class, 'getProvinces']);
Route::get('/api/cities', [\App\Http\Controllers\AddressController::class, 'getCities']);
Route::get('/api/barangays', [\App\Http\Controllers\AddressController::class, 'getBarangays']);
Route::get('/api/zip-code', [\App\Http\Controllers\AddressController::class, 'getZipCode']);
Route::get('/api/address-by-user', [\App\Http\Controllers\AddressController::class, 'getAddressByUser']);
Route::get('/api/organizations', function() {
    $deptId = request('department_id');
    $onlyUnassigned = request('unassigned');
    $query = \App\Models\Organization::query();
    if ($onlyUnassigned) {
        $query->whereNull('department_id');
    } elseif (!empty($deptId)) {
        $query->where('department_id', $deptId);
    }
    // Sort case-insensitively using collection sortBy (same as edit-staff page)
    return $query->get()->sortBy('name')->values();
});

// Organization Registration Request routes
Route::middleware(['auth', 'verified'])->prefix('student')->name('student.')->group(function () {
    Route::post('/organization-registration-request', [\App\Http\Controllers\Student\OrganizationRegistrationRequestController::class, 'store'])->name('organization-registration-request.store');
});

Route::middleware(['auth', 'verified', 'role:3'])->prefix('student-leader')->name('student-leader.')->group(function () {
    Route::get('/organization-requests', [\App\Http\Controllers\Student\OrganizationRegistrationRequestController::class, 'index'])->name('organization-requests.index');
    Route::post('/organization-requests/{id}/approve', [\App\Http\Controllers\Student\OrganizationRegistrationRequestController::class, 'approve'])->name('organization-requests.approve');
    Route::post('/organization-requests/{id}/decline', [\App\Http\Controllers\Student\OrganizationRegistrationRequestController::class, 'decline'])->name('organization-requests.decline');
});

// Student leader organization registration requests UI
Route::get('/student-leader/organization-requests', [App\Http\Controllers\AssistantOrganizationRequestController::class, 'index'])->name('student-leader.organization-requests.index');
Route::post('/student-leader/organization-requests/{id}/approve', [App\Http\Controllers\AssistantOrganizationRequestController::class, 'approve'])->name('student-leader.organization-requests.approve');
Route::post('/student-leader/organization-requests/{id}/decline', [App\Http\Controllers\AssistantOrganizationRequestController::class, 'decline'])->name('student-leader.organization-requests.decline');

// Student registration submission
Route::post('/student/register', [App\Http\Controllers\StudentRegisterController::class, 'submit'])->name('student.register.submit');

// General staff dashboard
Route::get('/admin/staff/dashboard', [\App\Http\Controllers\Admin\StaffDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin.staff.dashboard');

// Route for staff dashboard by designation
Route::get('/admin/staff/dashboard/{designation}', function($designation) {
    $user = auth()->user();
    // Get designation from User, StaffProfile, or Staff table (same logic as controller)
    $userDesignation = $user?->designation 
        ?? optional($user?->staffProfile)->designation 
        ?? Staff::where('email', $user->email)->value('designation');
    
    $role = (int)($user?->role ?? 0);
    $isAdmin = $role === 4;
    
    // Decode URL-encoded designation (Laravel should do this automatically, but be safe)
    $designation = urldecode($designation);
    
    $normalizedDesignation = trim($designation);
    // Normalize "Guidance Counsellor" (British spelling) to "Guidance Counselor" (American spelling) for consistency
    // This handles backward compatibility with existing data that may use British spelling
    if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
        $normalizedDesignation = 'Guidance Counselor';
    }
    
    $normalizedUserDesignation = trim($userDesignation ?? '');
    // Normalize "Guidance Counsellor" (British spelling) to "Guidance Counselor" (American spelling) for consistency
    // This handles backward compatibility with existing data that may use British spelling
    if (strcasecmp($normalizedUserDesignation, 'Guidance Counsellor') === 0) {
        $normalizedUserDesignation = 'Guidance Counselor';
    }
    
    // Only allow staff with matching designation or admin
    if (!$isAdmin && ($role !== 2 || strcasecmp($normalizedDesignation, $normalizedUserDesignation) !== 0)) {
        abort(403, 'Unauthorized access. Your designation: ' . ($userDesignation ?? 'not found') . ', Requested: ' . $designation);
    }
    
            // Special handling for Student Org. Moderator - redirect to their organizations
            if (strcasecmp(trim($normalizedDesignation), 'Student Org. Moderator') === 0) {
                return redirect()->route('staff.organizations.index');
            }
    
    // For all other designations (including Admission Services Officer), use the controller method
    // This will show the designation-dashboard.blade.php view
    // Use normalized designation to ensure consistency
    return app(\App\Http\Controllers\Admin\StaffDashboardController::class)->showByDesignation(request(), $normalizedDesignation);
})->middleware(['auth', 'verified'])->name('admin.staff.dashboard.designation');








