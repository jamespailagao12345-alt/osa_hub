<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function showStudentLeaders(Request $request)
    {
        // Build base query for student leaders: role in (1,3) or NULL
        $with = ['department', 'organization'];
        if (Schema::hasTable('organization_user')) {
            $with[] = 'otherOrganizations';
        }
        $query = \App\Models\User::with($with)
            ->where(function ($q) {
                $q->whereIn('role', [1, 3])
                  ->orWhereNull('role');
            });

        // Filters
        if ($request->filled('role_type')) {
            $roleType = $request->role_type;
            if ($roleType === 'student-leader') {
                $query->where('role', 3);
            } elseif ($roleType === 'student') {
                $query->where('role', 1);
            } elseif ($roleType === 'none') {
                $query->whereNull('role');
            }
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('organization_id')) {
            $orgId = $request->organization_id;
            $query->where(function ($qq) use ($orgId) {
                $qq->where('organization_id', $orgId);
                if (Schema::hasTable('organization_user')) {
                    $qq->orWhereHas('otherOrganizations', function ($q2) use ($orgId) {
                        $q2->where('organizations.id', $orgId);
                    });
                }
            });
        }

        $studentLeaders = $query->orderBy('last_name')->paginate(15)->appends($request->query());
        
        // Use cached reference data
        $departments = \App\Services\CacheService::getDepartments();
        $organizations = \App\Services\CacheService::getOrganizations();

        return view('admin.show-student-leaders', [
            'studentLeaders' => $studentLeaders,
            'departments' => $departments,
            'organizations' => $organizations,
            'filters' => $request->only(['role_type','department_id','organization_id'])
        ]);
    }

    public function showStudentsList(Request $request)
    {
        $query = \App\Models\User::with(['department', 'course'])
            ->where('role', 1);

        // Apply filters
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('year_level')) {
            $query->whereHas('studentInformation', function ($q) use ($request) {
                $q->where('year_level', $request->year_level);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(15)->appends($request->query());

        // Reference lists for filters - use cached data
        $departments = \App\Services\CacheService::getDepartments();
        $courses = \App\Services\CacheService::getCourses();

        return view('admin.show-students-list', [
            'students' => $students,
            'departments' => $departments,
            'courses' => $courses,
            'filters' => $request->only(['department_id','course_id','year_level','status'])
        ]);
    }

    public function showAdmins(Request $request)
    {
        $currentUser = auth()->user();
        
        $query = \App\Models\User::with(['department', 'organization'])
            ->where('role', 4)
            // Hide admin001 and all admin accounts from other admins/staff
            ->where('user_id', '!=', 'admin001');
        
        // If current user is not admin001, hide all admin accounts
        if (!$currentUser || $currentUser->user_id !== 'admin001') {
            // Hide all admin accounts (role 4) from non-admin001 users
            $query->whereRaw('1 = 0'); // This will return no results
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        $admins = $query->orderBy('last_name')->paginate(15)->appends($request->query());
        $departments = \App\Models\Department::orderBy('name')->get();
        $organizations = \App\Models\Organization::orderBy('name')->get();

        return view('admin.show-admins', [
            'admins' => $admins,
            'departments' => $departments,
            'organizations' => $organizations,
            'filters' => $request->only(['department_id','organization_id'])
        ]);
    }
    // ...existing code...
    public function profile()
    {
        $user = auth()->user();
        return view('admin.profile', compact('user'));
    }
    /**
     * Export filtered participants as CSV.
     */
    public function exportParticipants(Request $request)
    {
        $query = \App\Models\EventParticipant::with(['user', 'event']);
        if ($request->filled('date')) {
            $query->whereHas('event', function($q) use ($request) {
                $q->whereDate('event_date', $request->date);
            });
        }
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('course_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }
        if ($request->filled('year_level')) {
            $query->whereHas('user.studentInformation', function($q) use ($request) {
                $q->where('year_level', $request->year_level);
            });
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $participants = $query->get()->sortBy(function($participant) {
            $lastName = strtolower(optional($participant->user)->last_name ?? '');
            $firstName = strtolower(optional($participant->user)->first_name ?? '');
            return $lastName . ' ' . $firstName;
        })->values();

        $csv = "Event,Participant,Date,Status\n";
        foreach ($participants as $p) {
            $csv .= sprintf(
                '"%s","%s %s","%s","%s"\n',
                optional($p->event)->title ?? '-',
                optional($p->user)->first_name ?? '-',
                optional($p->user)->last_name ?? '',
                optional($p->event)->event_date ? optional($p->event)->event_date->format('Y-m-d') : '-',
                ucfirst($p->status ?? '')
            );
        }
        $filename = 'participants_export_' . now()->format('Ymd_His') . '.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename=' . $filename);
    }
    /**
     * Show participant history with filters.
     */
    public function participantsHistory(Request $request)
    {
        $query = \App\Models\EventParticipant::with(['user', 'event']);
        if ($request->filled('date')) {
            $query->whereHas('event', function($q) use ($request) {
                $q->whereDate('event_date', $request->date);
            });
        }
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }
        if ($request->filled('course_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('course_id', $request->course_id);
            });
        }
        if ($request->filled('year_level')) {
            $query->whereHas('user.studentInformation', function($q) use ($request) {
                $q->where('year_level', $request->year_level);
            });
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $participants = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Use cached reference data instead of loading all records
        $events = \App\Models\Event::select('id', 'name')->orderBy('name')->get();
        $users = \App\Models\User::select('id', 'first_name', 'last_name', 'email')->where('role', 1)->orderBy('last_name')->get();
        $departments = \App\Services\CacheService::getDepartments();
        $courses = \App\Services\CacheService::getCourses();
        
        return view('admin.participants-history', compact('participants', 'events', 'users', 'departments', 'courses'));
    }
    /**
     * Update event details (date, start time, end time).
     */
    public function updateEvent($id, Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $event = \App\Models\Event::findOrFail($id);
        
        // Allow admins to update any admin-created event, or allow creator to update their own event
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $eventCreator = \App\Models\User::find($event->created_by);
        $isAdminCreatedEvent = $eventCreator && (int) $eventCreator->role === 4;
        
        if ($event->created_by !== auth()->id() && !($isAdmin && $isAdminCreatedEvent)) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'required_student_participation' => 'nullable|boolean',
            'points' => 'nullable|integer|min:0',
            'attended_threshold_minutes' => 'nullable|integer|min:0',
            'late_threshold_minutes' => 'nullable|integer|min:0',
            'absent_threshold_minutes' => 'nullable|integer|min:0',
        ]);

        $description = $request->description;

        // Normalize time values to HH:MM:SS for MySQL strict mode
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        if (is_string($startTime) && preg_match('/^\d{2}:\d{2}$/', $startTime)) {
            $startTime .= ':00';
        }
        if (is_string($endTime) && preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            $endTime .= ':00';
        }

        // Build start/end as DATETIME
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $startDateTime = $startDate . ' ' . ($startTime ?: '00:00:00');
        $endDateTime = $endDate . ' ' . ($endTime ?: ($startTime ?: '23:59:59'));

        // Handle Required Student Participation toggle
        $requiredStudentParticipation = $request->has('required_student_participation') ? (bool) $request->required_student_participation : false;
        $oldRequiredParticipation = $event->required_student_participation ?? false;

        $updateData = [
            'name' => $request->name,
            'description' => $description,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'location' => $request->location,
            'required_student_participation' => $requiredStudentParticipation,
            'points' => $request->has('points') && $request->points !== '' ? (int) $request->points : null,
        ];

        // Update threshold settings if provided
        if ($request->has('attended_threshold_minutes')) {
            $updateData['attended_threshold_minutes'] = $request->attended_threshold_minutes;
        }
        if ($request->has('late_threshold_minutes')) {
            $updateData['late_threshold_minutes'] = $request->late_threshold_minutes;
        }
        if ($request->has('absent_threshold_minutes')) {
            $updateData['absent_threshold_minutes'] = $request->absent_threshold_minutes;
        }

        $event->update($updateData);

        // Generate or delete QR code based on toggle state
        if ($requiredStudentParticipation && !$oldRequiredParticipation) {
            // Toggle was turned ON - generate QR code
            try {
                $payload = [
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'start_date' => \Carbon\Carbon::parse($event->start_time)->format('Y-m-d'),
                    'end_date' => \Carbon\Carbon::parse($event->end_time)->format('Y-m-d'),
                    'location' => $event->location ?? 'N/A',
                ];
                
                $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(300)
                    ->generate(json_encode($payload));
                
                $qrCodePath = 'qr-codes/event-' . $event->id . '.svg';
                Storage::disk('public')->put($qrCodePath, $qrCode);
                
                $event->update(['qr_code_path' => $qrCodePath]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to generate QR code for event', [
                    'event_id' => $event->id,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif (!$requiredStudentParticipation && $oldRequiredParticipation) {
            // Toggle was turned OFF - delete QR code
            if ($event->qr_code_path && Storage::disk('public')->exists($event->qr_code_path)) {
                Storage::disk('public')->delete($event->qr_code_path);
            }
            $event->update(['qr_code_path' => null]);
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    public function destroyEvent($id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $event = \App\Models\Event::findOrFail($id);
        // Ensure only the creator can delete
        if ($event->created_by !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }
    /**
     * Show calendar with events that have all requirements approved.
     */
    public function calendar(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        // Get all approved events for the calendar
        $events = \App\Models\Event::where('status', 'approved')
            ->orderBy('start_time')
            ->get();
        
        // Group events by date for easier display
        $eventsByDate = $events->groupBy(function($event) {
            return \Carbon\Carbon::parse($event->start_time)->format('Y-m-d');
        });
        
        // Get year and month from request or use current year/month
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        
        // Create Carbon instance for the selected month
        $currentMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
        $monthName = $currentMonth->format('F');
        $displayYear = $currentMonth->year;
        
        // Organize events by semester for list view
        $firstSemStart = \Carbon\Carbon::createFromDate($year, 8, 31); // August 31
        $firstSemEnd = \Carbon\Carbon::createFromDate($year + 1, 1, 24); // January 24
        $secondSemStart = \Carbon\Carbon::createFromDate($year + 1, 2, 2); // February 2
        $secondSemEnd = \Carbon\Carbon::createFromDate($year + 1, 6, 18); // June 18
        $midyearStart = \Carbon\Carbon::createFromDate($year + 1, 7, 1); // July 1
        $midyearEnd = \Carbon\Carbon::createFromDate($year + 1, 8, 10); // August 10
        
        // Filter events for this academic year
        $yearEvents = $events->filter(function($event) use ($year) {
            $eventYear = \Carbon\Carbon::parse($event->start_time)->year;
            return $eventYear == $year || $eventYear == $year + 1;
        });
        
        // Organize events by name/description and semester
        $eventsByActivity = [];
        foreach ($yearEvents as $event) {
            $eventStart = \Carbon\Carbon::parse($event->start_time);
            $eventEnd = \Carbon\Carbon::parse($event->end_time);
            $activityKey = $event->name; // Use event name as activity key
            
            if (!isset($eventsByActivity[$activityKey])) {
                $eventsByActivity[$activityKey] = [
                    'name' => $event->name,
                    'description' => $event->description,
                    'first_sem' => [],
                    'second_sem' => [],
                    'midyear' => []
                ];
            }
            
            // Determine which semester(s) this event belongs to
            // First Semester: Aug 31 - Jan 24
            if ($eventStart->lte($firstSemEnd) && $eventEnd->gte($firstSemStart)) {
                $eventsByActivity[$activityKey]['first_sem'][] = [
                    'event' => $event,
                    'start' => $eventStart,
                    'end' => $eventEnd
                ];
            }
            
            // Second Semester: Feb 2 - Jun 18
            if ($eventStart->lte($secondSemEnd) && $eventEnd->gte($secondSemStart)) {
                $eventsByActivity[$activityKey]['second_sem'][] = [
                    'event' => $event,
                    'start' => $eventStart,
                    'end' => $eventEnd
                ];
            }
            
            // Midyear Term: Jul 1 - Aug 10
            if ($eventStart->lte($midyearEnd) && $eventEnd->gte($midyearStart)) {
                $eventsByActivity[$activityKey]['midyear'][] = [
                    'event' => $event,
                    'start' => $eventStart,
                    'end' => $eventEnd
                ];
            }
        }
        
        // Calculate previous and next month
        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();
        
        return view('admin.calendar', [
            'events' => $events,
            'eventsByDate' => $eventsByDate,
            'eventsByActivity' => $eventsByActivity,
            'year' => $year,
            'month' => $month,
            'currentMonth' => $currentMonth,
            'monthName' => $monthName,
            'displayYear' => $displayYear,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth
        ]);
    }
    /**
     * Bulk upload event requirement files.
     */
    public function bulkUploadRequirements(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $request->validate([
            'bulk_files' => 'required',
            'bulk_files.*' => 'file',
        ]);
        $uploaded = [];
        foreach ($request->file('bulk_files') as $file) {
            $path = $file->store('event_requirements', 'public');
            $uploaded[] = $path;
            // Optionally, associate with EventRequirement model here
        }
        return back()->with('success', count($uploaded) . ' files uploaded successfully.');
    }

    /**
     * Bulk download all event requirement files as a zip.
     */
    public function bulkDownloadRequirements()
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $requirements = \App\Models\EventRequirement::whereNotNull('file_path')->get();
        if ($requirements->isEmpty()) {
            return back()->with('error', 'No event requirement files found to download.');
        }
        $zip = new \ZipArchive();
        $zipFile = storage_path('app/public/event_requirements.zip');
        $added = false;
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($requirements as $req) {
                $filePath = storage_path('app/public/' . $req->file_path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                    $added = true;
                }
            }
            $zip->close();
        }
        if (!$added || !file_exists($zipFile)) {
            return back()->with('error', 'No event requirement files found to download.');
        }
        return response()->download($zipFile)->deleteFileAfterSend(true);
    }
    public function index()
    {
        $pendingEvents = \App\Models\Event::where('status', 'pending')
            ->with(['creator', 'organization'])
            ->get();
        $approvedEvents = \App\Models\Event::where('status', 'approved')->with('creator')->get();
        $staff = \App\Models\User::where('role', 2)->get();
        $appointments = \App\Models\Appointment::where('status', 'pending')->with('user', 'assignedStaff')->get();
        
        return view('admin.dashboard', compact('pendingEvents', 'approvedEvents', 'staff', 'appointments'));
    }
    
    /**
     * Display a listing of all files (admin view).
     */
    public function filesIndex(Request $request)
    {
        // Get all organizations for filter dropdown
        $organizations = \App\Models\Organization::orderBy('name')->get();
        
        // Get all file types for filter dropdown (before filtering)
        $allOrganizationFiles = \App\Models\OrganizationFile::select('file_type')->distinct()->whereNotNull('file_type')->get();
        $allStaffFiles = \App\Models\StaffOrganizationFile::select('file_type')->distinct()->whereNotNull('file_type')->get();
        $fileTypes = collect()
            ->merge($allOrganizationFiles->pluck('file_type'))
            ->merge($allStaffFiles->pluck('file_type'))
            ->unique()
            ->sort()
            ->values();
        
        // Get all organization files
        $organizationFilesQuery = \App\Models\OrganizationFile::with(['organization', 'uploader']);
        
        // Get all staff organization files with staff and designation
        // Join with users and staff tables to get designation
        $staffFilesQuery = \App\Models\StaffOrganizationFile::with(['organization', 'uploader', 'staff'])
            ->join('users', 'staff_organization_files.staff_id', '=', 'users.id')
            ->leftJoin('staff', 'staff.email', '=', 'users.email')
            ->select('staff_organization_files.*', 'staff.designation');
        
        // Filter by organization if provided
        if ($request->has('organization_id') && $request->organization_id) {
            $organizationFilesQuery->where('organization_files.organization_id', $request->organization_id);
            $staffFilesQuery->where('staff_organization_files.organization_id', $request->organization_id);
        }
        
        // Filter by file type if provided
        if ($request->has('file_type') && $request->file_type) {
            $organizationFilesQuery->where('organization_files.file_type', $request->file_type);
            $staffFilesQuery->where('staff_organization_files.file_type', $request->file_type);
        }
        
        // Get organization files (OrganizationFile model)
        $organizationFiles = $organizationFilesQuery->get();
        
        // Get staff organization files (StaffOrganizationFile model)
        $staffOrgFiles = $staffFilesQuery->get();
        
        // Add a flag to identify file type for routing
        $organizationFiles = $organizationFiles->map(function($file) {
            $file->is_staff_org_file = false;
            return $file;
        });
        
        $staffOrgFiles = $staffOrgFiles->map(function($file) {
            $file->is_staff_org_file = true;
            return $file;
        });
        
        // Combine both types of organization files and group by organization
        $allOrgFiles = $organizationFiles->concat($staffOrgFiles)->sortBy(function($file) {
            return $file->organization ? strtolower($file->organization->name) : '';
        })->values();
        
        // Group combined organization files by organization
        $organizationFilesGrouped = $allOrgFiles->groupBy(function($file) {
            return $file->organization ? $file->organization->id : 'no-org';
        })->map(function($files, $orgId) {
            $org = $files->first()->organization;
            return [
                'organization' => $org,
                'files' => $files->sortBy('created_at')->values(),
                'count' => $files->count()
            ];
        })->sortBy(function($group) {
            return $group['organization'] ? strtolower($group['organization']->name) : '';
        });
        
        // Get staff files grouped by staff member (not by organization)
        // Use the already-mapped $staffOrgFiles collection to maintain the is_staff_org_file flag
        $staffFiles = $staffOrgFiles->sortBy(function($file) {
            $staffName = $file->staff ? strtolower($file->staff->first_name . ' ' . $file->staff->last_name) : '';
            $designation = isset($file->designation) && $file->designation ? strtolower($file->designation) : '';
            return $staffName . '|' . $designation;
        })->values();
        
        // Group staff files by staff member
        $staffFilesGrouped = $staffFiles->groupBy('staff_id')->map(function($files, $staffId) {
            $staff = $files->first()->staff;
            $designation = isset($files->first()->designation) ? $files->first()->designation : '';
            return [
                'staff' => $staff,
                'designation' => $designation,
                'files' => $files->sortBy('created_at')->values(),
                'count' => $files->count()
            ];
        })->sortBy(function($group) {
            $staffName = $group['staff'] ? strtolower($group['staff']->first_name . ' ' . $group['staff']->last_name) : '';
            $designation = $group['designation'] ? strtolower($group['designation']) : '';
            return $staffName . '|' . $designation;
        });
        
        // Get admin files sorted by date created (newest first)
        $adminFiles = \App\Models\AdminFile::with('uploader')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Calculate total counts for badges
        $totalOrgFiles = $organizationFilesGrouped->sum(function($group) { return $group['count']; });
        $totalStaffFiles = $staffFilesGrouped->sum(function($group) { return $group['count']; });
        
        return view('admin.files.index', compact('organizationFilesGrouped', 'staffFilesGrouped', 'organizations', 'fileTypes', 'adminFiles', 'totalOrgFiles', 'totalStaffFiles'));
    }
    
    /**
     * Upload an admin file.
     */
    public function uploadAdminFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,xlsx,xls,csv,txt|max:51200', // 50MB max
            'file_category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        
        $file = $request->file('file');
        $user = auth()->user();
        
        // Sanitize filename
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        
        // Store file in admin files folder
        $folderPath = 'admin/files';
        $filePath = $file->storeAs($folderPath, $filename, 'public');
        
        // Get file info
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();
        $fileType = $this->detectAdminFileType($mimeType, $file->getClientOriginalExtension());
        
        try {
            \App\Models\AdminFile::create([
                'uploaded_by' => $user->id,
                'file_name' => $originalName,
                'file_path' => $filePath,
                'file_type' => $fileType,
                'file_category' => $request->input('file_category', 'Other'),
                'description' => $request->input('description'),
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
            ]);
            
            return redirect()->route('admin.files.index')->with('success', 'File uploaded successfully.');
        } catch (\Exception $e) {
            \Log::error('Admin file upload error: ' . $e->getMessage());
            return redirect()->route('admin.files.index')->with('error', 'Failed to upload file: ' . $e->getMessage());
        }
    }
    
    /**
     * Download an admin file.
     */
    public function downloadAdminFile($fileId)
    {
        $file = \App\Models\AdminFile::findOrFail($fileId);
        
        $path = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        
        return response()->download($path, $file->file_name);
    }
    
    /**
     * Delete an admin file.
     */
    public function deleteAdminFile($fileId)
    {
        $file = \App\Models\AdminFile::findOrFail($fileId);
        
        // Delete file from storage
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        // Delete database record
        $file->delete();
        
        return redirect()->route('admin.files.index')->with('success', 'File deleted successfully.');
    }
    
    /**
     * Approve an admin file.
     */
    public function approveAdminFile($fileId)
    {
        $file = \App\Models\AdminFile::findOrFail($fileId);
        
        // Update file status to approved
        $file->status = 'approved';
        $file->save();
        
        return redirect()->route('admin.files.index')->with('success', 'File approved successfully.');
    }
    
    /**
     * Detect file type for admin files.
     */
    private function detectAdminFileType($mimeType, $extension)
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $documentTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $spreadsheetTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        if (in_array($mimeType, $imageTypes) || in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif'])) {
            return 'image';
        } elseif (in_array($mimeType, $documentTypes) || in_array(strtolower($extension), ['pdf', 'doc', 'docx'])) {
            return 'document';
        } elseif (in_array($mimeType, $spreadsheetTypes) || in_array(strtolower($extension), ['xls', 'xlsx', 'csv'])) {
            return 'spreadsheet';
        } else {
            return 'other';
        }
    }
    
    /**
     * Download an organization file (admin access).
     */
    public function downloadOrganizationFile($fileId)
    {
        $file = \App\Models\OrganizationFile::findOrFail($fileId);
        
        $path = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        
        return response()->download($path, $file->file_name);
    }
    
    /**
     * Download a staff organization file (admin access).
     */
    public function downloadStaffFile($fileId)
    {
        $file = \App\Models\StaffOrganizationFile::findOrFail($fileId);
        
        $path = storage_path('app/public/' . $file->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        
        return response()->download($path, $file->file_name);
    }
    
    /**
     * Delete an organization file (admin access).
     */
    public function deleteOrganizationFile($fileId)
    {
        $file = \App\Models\OrganizationFile::findOrFail($fileId);
        
        // Delete file from storage
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        // Delete database record
        $file->delete();
        
        return redirect()->route('admin.files.index')->with('success', 'File deleted successfully.');
    }
    
    /**
     * Delete a staff organization file (admin access).
     */
    public function deleteStaffFile($fileId)
    {
        $file = \App\Models\StaffOrganizationFile::findOrFail($fileId);
        
        // Delete file from storage
        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        // Delete database record
        $file->delete();
        
        return redirect()->route('admin.files.index')->with('success', 'File deleted successfully.');
    }
    
    /**
     * Approve an organization file (admin access).
     */
    public function approveOrganizationFile($fileId)
    {
        $file = \App\Models\OrganizationFile::findOrFail($fileId);
        
        // Update file status to approved
        $file->status = 'approved';
        $file->save();
        
        return redirect()->route('admin.files.index')->with('success', 'File approved successfully.');
    }
    
    /**
     * Approve a staff organization file (admin access).
     */
    public function approveStaffFile($fileId)
    {
        $file = \App\Models\StaffOrganizationFile::findOrFail($fileId);
        
        // Update file status to approved
        $file->status = 'approved';
        $file->save();
        
        return redirect()->route('admin.files.index')->with('success', 'File approved successfully.');
    }
    
    /**
     * Display organizational structure page
     */
    public function organizationalStructure(Request $request)
    {
        $organizationId = $request->get('organization_id');
        
        if ($organizationId) {
            // Show Staff → Assistants structure for specific organization
            $organization = \App\Models\Organization::findOrFail($organizationId);
            $orgStructure = $this->buildOrgStaffAssistantsStructure($organizationId);
            $structureType = 'organization';
            
            return view('admin.organizational-structure', compact('orgStructure', 'organization', 'structureType'));
        } else {
            // Show Admin → Staff structure
            $orgStructure = $this->buildAdminStaffOrgStructure();
            $structureData = $this->buildAdminStaffOrgStructureData();
            $structureType = 'admin';
            
            return view('admin.organizational-structure', compact('orgStructure', 'structureData', 'structureType'));
        }
    }
    
    /**
     * Update organizational structure configuration
     */
    public function updateOrgStructureConfig(Request $request)
    {
        $request->validate([
            'staff_selections' => 'required|array',
            'staff_selections.*' => 'array',
            'staff_selections.*.*' => 'required|integer|exists:staff,id',
            'max_levels' => 'required|integer|min:1|max:10'
        ]);
        
        $config = \App\Models\OrgStructureConfig::getDefaultConfig();
        $config->staff_selections = $request->staff_selections;
        $config->max_levels = $request->max_levels;
        $config->save();
        
        return redirect()->back()->with('success', 'Organizational structure configuration updated successfully!');
    }

    public function showStaff()
    {
        $currentUser = auth()->user();
        
        // Fetch all staff including Student Org Moderator - no filtering by designation
        // Eager load user relationship to access designation from users table if needed
        $staff = \App\Models\Staff::with(['department', 'organizations', 'admin', 'user'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $assistants = \App\Models\User::where('role', 3)->orderBy('last_name')->orderBy('first_name')->get();
        $students = \App\Models\User::where('role', 1)->orderBy('last_name')->orderBy('first_name')->get();
        
        // Hide admin accounts from other admins/staff
        $adminsQuery = \App\Models\User::where('role', 4);
        if (!$currentUser || $currentUser->user_id !== 'admin001') {
            // Hide all admin accounts (including admin001) from non-admin001 users
            $adminsQuery->whereRaw('1 = 0'); // This will return no results
        }
        $admins = $adminsQuery->orderBy('last_name')->orderBy('first_name')->get();
        
        return view('admin.show-staff', compact('staff', 'assistants', 'students', 'admins'));
    }

    /**
     * Show organizations management page
     */
    public function organizations()
    {
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        
        // Check if user is OSA Staff
        $isOSAStaff = false;
        if ($user && (int) $user->role === 2) {
            // Try to find staff record by email (case-insensitive)
            $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            $normalizedDesignation = trim($userDesignation ?? '');
            $isOSAStaff = strcasecmp($normalizedDesignation, 'OSA Staff') === 0;
        }
        
        // Only allow Admin (role 4) or OSA Staff (role 2 with designation "OSA Staff")
        if (!$isAdmin && !$isOSAStaff) {
            // Redirect to designated dashboard
            if ($user && (int) $user->role === 2) {
                $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
                $userDesignation = $user->designation
                    ?? optional($user->staffProfile)->designation
                    ?? ($staffRecord ? $staffRecord->designation : null);
                
                if ($userDesignation) {
                    return redirect()->route('admin.staff.dashboard.designation', ['designation' => $userDesignation])
                        ->with('error', 'You do not have access to this page.');
                }
            }
            return redirect()->route('admin.staff.dashboard')
                ->with('error', 'You do not have access to this page.');
        }
        
        $organizations = \App\Models\Organization::with('department')->orderBy('name')->get();
        return view('admin.organizations', compact('organizations'));
    }

    /**
     * Update organization official email
     */
    public function updateOrganizationEmail(Request $request, $id)
    {
        $request->validate([
            'official_email' => 'required|email|max:255',
        ]);

        $organization = \App\Models\Organization::findOrFail($id);
        $organization->update([
            'official_email' => $request->official_email,
        ]);

        return back()->with('success', 'Official email updated successfully.');
    }

    /**
     * Update organization details
     * Accessible to staff (role 2), assistants (role 3), and admins (role 4)
     */
    public function updateOrganization(Request $request, $id)
    {
        $user = auth()->user();
        $userRole = (int) ($user->role ?? 0);
        
        // Check if user has permission (staff, assistant, or admin)
        if (!in_array($userRole, [2, 3, 4])) {
            abort(403, 'Unauthorized: Only staff, assistants, and admins can update organization details.');
        }
        
        // For staff and assistants, verify they are assigned to this organization
        if (in_array($userRole, [2, 3])) {
            $organization = \App\Models\Organization::findOrFail($id);
            $isAssigned = false;
            
            // Check if staff is assigned to this organization
            if ($userRole === 2) {
                $staff = \App\Models\Staff::where('email', $user->email)->first();
                if ($staff) {
                    // Check direct assignment
                    if ($staff->organization_id == $organization->id) {
                        $isAssigned = true;
                    }
                    // Check many-to-many relationship
                    if (!$isAssigned && $staff->organizations()->where('organizations.id', $organization->id)->exists()) {
                        $isAssigned = true;
                    }
                }
                // Check user's organization_id
                if (!$isAssigned && $user->organization_id == $organization->id) {
                    $isAssigned = true;
                }
                // Check otherOrganizations relationship
                if (!$isAssigned && $user->otherOrganizations()->where('organizations.id', $organization->id)->exists()) {
                    $isAssigned = true;
                }
            }
            
            // Check if assistant is assigned to this organization
            if ($userRole === 3 || ($userRole === 1 && $user->assistantAssignments()->where('is_active', true)->exists())) {
                $assignments = \App\Models\AssistantAssignment::where('user_id', $user->id)
                    ->where('organization_id', $organization->id)
                    ->where('is_active', true)
                    ->exists();
                if ($assignments) {
                    $isAssigned = true;
                }
            }
            
            if (!$isAssigned) {
                abort(403, 'Unauthorized: You are not assigned to this organization.');
            }
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'acronym' => 'nullable|string|max:50',
            'mailing_address' => 'nullable|string|max:500',
            'official_email' => 'nullable|email|max:255',
            'date_established' => 'nullable|date',
            'staff_id' => 'nullable|exists:staff_information,id',
        ]);

        $organization = \App\Models\Organization::findOrFail($id);
        
        // Convert empty strings to null for nullable fields
        $updateData = [
            'name' => $request->name,
            'department_id' => $request->filled('department_id') ? $request->department_id : null,
            'acronym' => $request->filled('acronym') ? $request->acronym : null,
            'mailing_address' => $request->filled('mailing_address') ? $request->mailing_address : null,
            'official_email' => $request->filled('official_email') ? $request->official_email : null,
            'date_established' => $request->filled('date_established') ? $request->date_established : null,
        ];
        
        $organization->update($updateData);
        
        // Handle staff assignment
        if ($request->filled('staff_id')) {
            $organization->staff()->syncWithoutDetaching([$request->staff_id]);
        } elseif ($request->has('staff_id') && $request->staff_id === '') {
            // If staff_id is explicitly empty, don't change existing assignments
            // This allows clearing the selection without removing existing staff
        }

        // Refresh the organization to ensure we have the latest data
        $organization->refresh();
        $organization->load('department');

        return redirect()->route('admin.organizations.profile', $id)->with('success', 'Organization details updated successfully.');
    }

    /**
     * Show organization profile with student count
     */
    public function organizationProfile($id, Request $request)
    {
        $organization = \App\Models\Organization::with(['department', 'users', 'otherUsers', 'staff.department'])->findOrFail($id);
        
        // Get all staff from staff_information table
        $allStaff = \App\Models\Staff::with(['department', 'user'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        // Count all students (role 1) who belong to this organization
        // This includes both direct users and users via pivot table
        $directStudents = $organization->users()->where('role', 1)->get();
        $pivotStudents = $organization->otherUsers()->where('role', 1)->get();
        
        // Combine and get unique students, then sort alphabetically
        $allStudents = $directStudents->merge($pivotStudents)->unique('id')
            ->sortBy(function($student) {
                return strtolower(($student->last_name ?? '') . ' ' . ($student->first_name ?? ''));
            })->values();
        
        // Get total student count before filtering
        $studentCount = $allStudents->count();
        
        // Apply search filters if provided
        $searchTerm = $request->get('search', '');
        $yearLevel = $request->get('year_level', '');
        
        if ($searchTerm || $yearLevel) {
            $allStudents = $allStudents->filter(function($student) use ($searchTerm, $yearLevel) {
                $matches = true;
                
                // Search by student_id, name
                if ($searchTerm) {
                    $searchLower = strtolower($searchTerm);
                    $studentId = strtolower($student->user_id ?? '');
                    $firstName = strtolower($student->first_name ?? '');
                    $lastName = strtolower($student->last_name ?? '');
                    $middleName = strtolower($student->middle_name ?? '');
                    $fullName = $firstName . ' ' . $middleName . ' ' . $lastName;
                    
                    $matches = $matches && (
                        strpos($studentId, $searchLower) !== false ||
                        strpos($firstName, $searchLower) !== false ||
                        strpos($lastName, $searchLower) !== false ||
                        strpos($middleName, $searchLower) !== false ||
                        strpos($fullName, $searchLower) !== false
                    );
                }
                
                // Filter by year level
                if ($yearLevel) {
                    $matches = $matches && ($student->year_level == $yearLevel);
                }
                
                return $matches;
            })->values();
        }
        
        // Get organization files grouped by category
        $files = \App\Models\OrganizationFile::where('organization_id', $id)
            ->with('uploader')
            ->get()
            ->groupBy('file_category');
        
        // Define required file categories
        $requiredFileCategories = [
            'accreditation_checklist' => 'Accreditation of New Organization Checklist',
            'application_letter' => 'Application Letter for Student Organization',
            'accreditation_form' => 'Application for Accreditation/Reaccreditation of School Organization',
            'concept_paper' => 'Concept Paper_Student Organization',
            'constitution' => 'Constitution and By-laws_for (Org.Name)',
            'organizational_profile' => 'Organizational Profile',
            'officers_members_list' => 'List of Officers and Members',
            'personal_data_sheet_assistant' => 'Personal Data Sheet (each student leader)',
            'personal_data_sheet' => 'Annual Work and Financial Plan',
            'moderatorship_letter' => 'Moderatorship-Acceptance Letter',
        ];
        
        // Get all departments for the edit form
        $departments = \App\Services\CacheService::getDepartments();
        
        return view('admin.organization-profile', compact('organization', 'studentCount', 'allStudents', 'files', 'requiredFileCategories', 'allStaff', 'departments'));
    }

    public function approveEvent($id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $event = \App\Models\Event::with('organization')->findOrFail($id);
        $event->update(['status' => 'approved']);
        
        
        $currentUser = auth()->user();
        $staffQuery = \App\Models\User::where('id', $event->created_by);
        // Only include admins if current user is admin001
        if ($currentUser && $currentUser->user_id === 'admin001') {
            $staffQuery->orWhere('role', 4);
        }
        $staff = $staffQuery->get();
        foreach ($staff as $user) {
            $user->notify(new \App\Notifications\EventApprovedNotification($event));
        }
        
        // Send email to organization's official email
        if ($event->organization && $event->organization->official_email) {
            try {
                \Illuminate\Support\Facades\Mail::to($event->organization->official_email)
                    ->send(new \App\Mail\EventApprovedMail($event));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send event approval email to organization', [
                    'event_id' => $event->id,
                    'organization_id' => $event->organization->id,
                    'email' => $event->organization->official_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return back()->with('success', 'Event approved and notifications sent.');
    }

    public function declineEvent(Request $request, $id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $event = \App\Models\Event::with(['organization', 'creator'])->findOrFail($id);
        $event->update([
            'status' => 'declined',
            'decline_reason' => $request->reason,
        ]);
        
        // Send email to organization's official email (queued)
        $emailQueued = false;
        if ($event->organization && $event->organization->official_email) {
            try {
                \Illuminate\Support\Facades\Mail::to($event->organization->official_email)
                    ->queue(new \App\Mail\EventDeclinedMail($event));
                $emailQueued = true;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue event decline email to organization', [
                    'event_id' => $event->id,
                    'organization_id' => $event->organization->id,
                    'email' => $event->organization->official_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // Send in-app notifications to organization users and staff
        if ($event->organization) {
            // Get all users directly associated with the organization
            $organizationUsers = \App\Models\User::where('organization_id', $event->organization->id)->get();
            
            // Get users via pivot table (additional organizations)
            $otherUsers = $event->organization->otherUsers()->get();
            
            // Get staff members assigned to this organization
            $staffMembers = \App\Models\Staff::where('organization_id', $event->organization->id)->get();
            $staffUsers = collect();
            foreach ($staffMembers as $staff) {
                $staffUser = \App\Models\User::where('email', $staff->email)->first();
                if ($staffUser) {
                    $staffUsers->push($staffUser);
                }
            }
            
            // Combine all unique users
            $allUsers = $organizationUsers->concat($otherUsers)->concat($staffUsers)->unique('id');
            
            // Send notifications to all users
            foreach ($allUsers as $user) {
                try {
                    $user->notify(new \App\Notifications\EventDeclinedNotification($event));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send event decline notification to user', [
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
            
            // Also notify the event creator if they exist and are not already in the list
            if ($event->creator && !$allUsers->contains('id', $event->creator->id)) {
                try {
                    $event->creator->notify(new \App\Notifications\EventDeclinedNotification($event));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send event decline notification to event creator', [
                        'event_id' => $event->id,
                        'creator_id' => $event->creator->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        // Handle redirect based on return_to parameter
        if ($request->has('return_to')) {
            $backUrl = $this->validateReturnUrl($request->return_to);
            if ($backUrl) {
                return redirect($backUrl)->with('success', 'Event declined successfully. ' . ($emailQueued ? 'Email notification has been queued.' : '') . ' In-app notifications sent to organization members.');
            }
        }
        
        return redirect()->route('admin.events.show', $event->id)->with('success', 'Event declined successfully. ' . ($emailQueued ? 'Email notification has been queued.' : '') . ' In-app notifications sent to organization members. The event is now closed and cannot be edited or updated.');
    }

    public function addRequirement(Request $request, $id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $request->validate([
            'requirement_name' => 'required|string|max:255',
        ]);

        $event = \App\Models\Event::with('organization')->findOrFail($id);
        
        // Prevent adding requirements if event is declined
        if ($event->status === 'declined') {
            return back()->with('error', 'Cannot add requirements to a declined event.');
        }

        \App\Models\EventRequirement::create([
            'event_id' => $event->id,
            'requirement_name' => $request->requirement_name,
            'is_uploaded' => false,
        ]);

        // Notify organization about missing requirements
        $this->notifyMissingRequirements($event);

        return back()->with('success', 'Requirement added successfully.');
    }

    /**
     * Notify organization about missing requirements for an event
     */
    private function notifyMissingRequirements($event)
    {
        if (!$event->organization || !$event->organization->official_email) {
            return;
        }

        // Default requirements list
        $defaultRequirements = [
            'Student Activity Request Form',
            'Program Flow',
            'Letters of Approval',
            'Financial Report',
            'Accomplishment Report'
        ];

        // Get forwarded requirements for this event
        $forwardedRequirements = $event->requirements()->pluck('requirement_name')->toArray();
        
        // Check which default requirements are missing
        $missingRequirements = collect($defaultRequirements)->filter(function($req) use ($forwardedRequirements) {
            return !in_array($req, $forwardedRequirements);
        });

        // Also check if admin-added requirements are not uploaded
        $adminRequirements = $event->requirements()
            ->whereNotIn('requirement_name', $defaultRequirements)
            ->where('is_uploaded', false)
            ->pluck('requirement_name');

        $allMissing = $missingRequirements->merge($adminRequirements);

        // Send email if there are missing requirements
        if ($allMissing->isNotEmpty()) {
            try {
                \Illuminate\Support\Facades\Mail::to($event->organization->official_email)
                    ->send(new \App\Mail\EventRequirementsMissingMail($event, $allMissing));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send missing requirements email to organization', [
                    'event_id' => $event->id,
                    'organization_id' => $event->organization->id,
                    'email' => $event->organization->official_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function appointments(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        
       
        $userDesignation = $user->designation 
            ?? optional($user->staffProfile)->designation 
            ?? \App\Models\Staff::where('email', $user->email)->value('designation');
        
       
        if ($userDesignation && strcasecmp(trim($userDesignation), 'Guidance Counsellor') === 0) {
            $userDesignation = 'Guidance Counselor';
        }
        
        $isOSAStaff = $userDesignation && strcasecmp($userDesignation, 'OSA Staff') === 0;
        
        
        $query = \App\Models\Appointment::with(['user', 'assignedStaff'])
            ->where(function($q) {
                $q->where('session', '!=', 'Finish')
                  ->orWhereNull('session');
            })
            ->orderByRaw('COALESCE(rescheduled_date, appointment_date) ASC')
            ->orderByRaw('COALESCE(rescheduled_time, appointment_time) ASC')
            ->orderBy('created_at', 'desc'); 

        // Initialize filterAssigned to avoid undefined variable error
        $filterAssigned = null;

        // If staff (including Admission Services Officer or OSA Staff), show only appointments assigned to them
        if ($isStaff && !$isAdmin) {
            $query->where('assigned_staff_id', $user->id);
        } else {
            // Filter by assigned staff (for admins only)
        $filterAssigned = $request->query('assigned_staff_id');
        if ($request->has('assigned_staff_id') && $filterAssigned !== null && $filterAssigned !== '') {
            if ($filterAssigned === 'unassigned') {
                $query->whereNull('assigned_staff_id');
            } elseif (is_numeric($filterAssigned)) {
                $query->where('assigned_staff_id', (int) $filterAssigned);
                }
            }
        }

        $appointments = $query->paginate(10)->appends($request->query());
        
        // Patients History: Finished appointments, sorted by student and faculty
        $patientsHistoryQuery = \App\Models\Appointment::with(['user', 'assignedStaff'])
            ->where('session', 'Finish');
        
        // Apply same staff filter for Patients History
        if ($isStaff && !$isAdmin) {
            $patientsHistoryQuery->where('assigned_staff_id', $user->id);
        } else {
            if ($request->has('assigned_staff_id') && $filterAssigned !== null && $filterAssigned !== '') {
                if ($filterAssigned === 'unassigned') {
                    $patientsHistoryQuery->whereNull('assigned_staff_id');
                } elseif (is_numeric($filterAssigned)) {
                    $patientsHistoryQuery->where('assigned_staff_id', (int) $filterAssigned);
                }
            }
        }
        
        // Sort by role: students (role 1) first, then faculty (role 2), then others
        // Within each role group, sort by full name
        $patientsHistory = $patientsHistoryQuery->get()->sortBy([
            function($appointment) {
                $userRole = $appointment->user ? (int) $appointment->user->role : 999;
                // Students (1) come first, then faculty (2), then others
                return $userRole === 1 ? 0 : ($userRole === 2 ? 1 : 2);
            },
            function($appointment) {
                return strtolower($appointment->full_name ?? '');
            }
        ])->values();
        
        $staffList = \App\Models\User::where('role', 2)->orderBy('first_name')->get();
        return view('admin.appointments', compact('appointments', 'patientsHistory', 'staffList', 'filterAssigned', 'isOSAStaff', 'isAdmin', 'isStaff', 'userDesignation'));
    }

    public function approveAppointment($id, Request $request)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        
        // Check if user has permission to approve this appointment
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $userDesignation = $user->designation 
            ?? optional($user->staffProfile)->designation 
            ?? \App\Models\Staff::where('email', $user->email)->value('designation');
        $isOSAStaff = $userDesignation && strcasecmp($userDesignation, 'OSA Staff') === 0;
        
        // Only allow if admin or if staff (including Admission Services Officer or OSA Staff) and appointment is assigned to them
        $isStaff = $user && (int) $user->role === 2;
        if (!$isAdmin && !($isStaff && $appointment->assigned_staff_id == $user->id)) {
            return back()->with('error', 'Unauthorized: You do not have permission to approve this appointment.');
        }
        
        $appointment->update([
            'status' => 'approved',
            'action_taken' => 'approve',
        ]);

        // Refresh the appointment model to ensure we have the latest data
        $appointment->refresh();

        // Queue email notification to the appointment email address
        $emailQueued = false;
        if (!empty($appointment->email)) {
            try {
                // Queue email to the appointment email address (non-blocking)
                \Illuminate\Support\Facades\Mail::to($appointment->email)->queue(new \App\Mail\AppointmentApprovedMail($appointment));
                $emailQueued = true;
                
                \Illuminate\Support\Facades\Log::info('Approval email queued successfully', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue approval email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Illuminate\Support\Facades\Log::warning('Cannot queue approval email: appointment email is empty', [
                'appointment_id' => $appointment->id,
            ]);
        }

        $message = 'Appointment approved successfully.';
        if ($emailQueued) {
            $message .= ' Email notification has been queued.';
        } elseif (!empty($appointment->email)) {
            $message .= ' Note: Email notification could not be queued.';
        }

        return back()->with('success', $message);
    }

    public function declineAppointment($id, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $appointment = \App\Models\Appointment::findOrFail($id);
        
        // Check if user has permission to decline this appointment
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $userDesignation = $user->designation 
            ?? optional($user->staffProfile)->designation 
            ?? \App\Models\Staff::where('email', $user->email)->value('designation');
        $isOSAStaff = $userDesignation && strcasecmp($userDesignation, 'OSA Staff') === 0;
        
        // Only allow if admin or if staff (including Admission Services Officer or OSA Staff) and appointment is assigned to them
        $isStaff = $user && (int) $user->role === 2;
        if (!$isAdmin && !($isStaff && $appointment->assigned_staff_id == $user->id)) {
            return back()->with('error', 'Unauthorized: You do not have permission to decline this appointment.');
        }
        
        $appointment->update([
            'status' => 'declined',
            'action_taken' => 'decline',
            'action_reason' => $request->reason,
        ]);

        // Queue email notification to the appointment email address
        $emailQueued = false;
        if (!empty($appointment->email)) {
            try {
                // Queue email (non-blocking)
                \Illuminate\Support\Facades\Mail::to($appointment->email)->queue(new \App\Mail\AppointmentDeclinedMail($appointment));
                $emailQueued = true;
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue decline email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = 'Appointment declined successfully.';
        if ($emailQueued) {
            $message .= ' Email notification has been queued.';
        } elseif (!empty($appointment->email)) {
            $message .= ' Note: Email notification could not be queued.';
        }

        return back()->with('success', $message);
    }

    public function cancelAppointment($id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $appointment->update(['status' => 'cancelled']);
        return back()->with('success', 'Appointment cancelled.');
    }

    public function reassignAppointment($id, Request $request)
    {
        $request->validate([
            'assigned_staff_id' => 'required|exists:users,id'
        ]);

        $appointment = \App\Models\Appointment::findOrFail($id);
        $previousStaffId = $appointment->assigned_staff_id;
        $appointment->update([
            'assigned_staff_id' => $request->assigned_staff_id,
            'status' => 'pending',
        ]);

        // Notify previous staff if exists and is different from new
        if ($previousStaffId && $previousStaffId != $request->assigned_staff_id) {
            $previousStaff = \App\Models\User::find($previousStaffId);
            if ($previousStaff) {
                $previousStaff->notify(new \App\Notifications\AppointmentReassignedNotification($appointment));
            }
        }

        return back()->with('success', 'Appointment reassigned. Previous staff notified.');
    }

    public function rescheduleAppointment($id, Request $request)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $request->validate([
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required',
            'reschedule_reason' => 'nullable|string|max:500',
        ]);

        // Check if user has permission to reschedule this appointment
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $userDesignation = $user->designation 
            ?? optional($user->staffProfile)->designation 
            ?? \App\Models\Staff::where('email', $user->email)->value('designation');
        $isOSAStaff = $userDesignation && strcasecmp($userDesignation, 'OSA Staff') === 0;
        
        // Only allow if admin or if staff (including Admission Services Officer or OSA Staff) and appointment is assigned to them
        $isStaff = $user && (int) $user->role === 2;
        if (!$isAdmin && !($isStaff && $appointment->assigned_staff_id == $user->id)) {
            if ($request->has('return_to')) {
                $backUrl = $this->validateReturnUrl($request->return_to);
                if ($backUrl) {
                    return redirect($backUrl)->with('error', 'Unauthorized: You do not have permission to reschedule this appointment.');
                }
            }
            return back()->with('error', 'Unauthorized: You do not have permission to reschedule this appointment.');
        }

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'rescheduled', // Set status to rescheduled when rescheduling is successful
            'action_taken' => 'reschedule',
            'rescheduled_date' => $request->appointment_date,
            'rescheduled_time' => $request->appointment_time,
            'action_reason' => $request->reschedule_reason,
            'rescheduled_reminder_sent_at' => null, // Reset reminder flag so new reminder can be sent for rescheduled time
            'reminder_sent_at' => null, // Also reset original reminder flag in case appointment was previously approved
        ]);

        // Refresh the appointment model to ensure we have the latest data
        $appointment->refresh();

        // Send email notification to the appointment email address (queue it to avoid blocking)
        if (!empty($appointment->email)) {
            // Queue the email to prevent blocking the request if SMTP is slow
            // The email will be processed by the queue worker
            try {
                // Use the refreshed appointment to ensure email has latest data
                $mailJob = new \App\Mail\AppointmentRescheduledMail($appointment->fresh());
                // Use queue() which respects ShouldQueue interface
                \Illuminate\Support\Facades\Mail::to($appointment->email)->queue($mailJob);
            } catch (\Throwable $e) {
                // Catch any errors including timeouts
                \Illuminate\Support\Facades\Log::error('Failed to queue reschedule email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
                // Don't fail the request if email fails - appointment is already rescheduled
            }
        }

        if ($request->has('return_to')) {
            $backUrl = $this->validateReturnUrl($request->return_to);
            if ($backUrl) {
                // Redirect with success message - the page will reload and show updated appointment data
                return redirect($backUrl)->with('success', 'Appointment rescheduled successfully. The appointment has been updated with the new date and time.');
            }
        }
        // If no return_to, redirect back to appointments index
        return redirect()->route('admin.appointments.index')->with('success', 'Appointment rescheduled successfully. The appointment has been updated with the new date and time.');
    }

    public function updateSession($id, Request $request)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $request->validate([
            'session' => 'required|in:Finish,On Going',
        ]);

        // Check if user has permission to update this appointment
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        if (!$isAdmin && !($isStaff && $appointment->assigned_staff_id == $user->id)) {
            if ($request->has('return_to')) {
                $backUrl = $this->validateReturnUrl($request->return_to);
                if ($backUrl) {
                    return redirect($backUrl)->with('error', 'Unauthorized: You do not have permission to update this appointment.');
                }
            }
            return back()->with('error', 'Unauthorized: You do not have permission to update this appointment.');
        }

        $appointment->update([
            'session' => $request->session,
        ]);

        // Always return to the validated URL if return_to parameter exists
        if ($request->has('return_to')) {
            $backUrl = $this->validateReturnUrl($request->return_to);
            if ($backUrl) {
                return redirect($backUrl)->with('success', 'Session updated successfully.');
            }
        }
        return redirect()->route('admin.appointments.index')->with('success', 'Session updated successfully.');
    }
    
    /**
     * Update remarks for an appointment
     */
    public function updateRemarks($id, Request $request)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $request->validate([
            'remarks' => 'nullable|string|max:5000',
        ]);

        // Check if user has permission to update this appointment
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        
        if (!$isAdmin && !($isStaff && $appointment->assigned_staff_id == $user->id)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You do not have permission to update this appointment.',
                ], 403);
            }
            if ($request->has('return_to')) {
                $backUrl = $this->validateReturnUrl($request->return_to);
                if ($backUrl) {
                    return redirect($backUrl)->with('error', 'Unauthorized: You do not have permission to update this appointment.');
                }
            }
            return back()->with('error', 'Unauthorized: You do not have permission to update this appointment.');
        }

        $appointment->update([
            'remarks' => $request->remarks,
        ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Remarks updated successfully.',
            ]);
        }

        if ($request->has('return_to')) {
            $backUrl = $this->validateReturnUrl($request->return_to);
            if ($backUrl) {
                return redirect($backUrl)->with('success', 'Remarks updated successfully.');
            }
        }
        return back()->with('success', 'Remarks updated successfully.');
    }
    
    /**
     * Suspend a student account
     */
    public function suspendStudent($id, Request $request)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Check if user is a student (role = 1)
        if ((int) $user->role !== 1) {
            return back()->with('error', 'Only student accounts can be suspended.');
        }
        
        // Check if user has permission (only Prefect of Discipline, NOT Admin)
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        $isStaff = $currentUser && (int) $currentUser->role === 2;
        
        // Admins cannot suspend accounts, only view
        if ($isAdmin) {
            return back()->with('error', 'Unauthorized: Only staff with "Prefect of Discipline" designation can suspend student accounts. Admins can only view student details.');
        }
        
        if (!$isStaff) {
            return back()->with('error', 'Unauthorized: You do not have permission to suspend accounts.');
        }
        
        // Check if current user is Prefect of Discipline
        $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
        $userDesignation = $currentUser->designation
            ?? optional($currentUser->staffProfile)->designation
            ?? ($staffRecord ? $staffRecord->designation : null);
        
        if (!$userDesignation || strcasecmp($userDesignation, 'Prefect of Discipline') !== 0) {
            return back()->with('error', 'Unauthorized: Only Prefect of Discipline can suspend student accounts.');
        }
        
        // Validate suspension reason
        $request->validate([
            'suspension_reason' => 'required|string|min:10|max:1000',
        ]);
        
        $user->update([
            'suspended' => true,
            'suspension_reason' => $request->input('suspension_reason'),
        ]);
        
        return back()->with('success', 'Student account has been suspended successfully.');
    }
    
    /**
     * Reactivate a student account
     */
    public function reactivateStudent($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Check if user is a student (role = 1)
        if ((int) $user->role !== 1) {
            return back()->with('error', 'Only student accounts can be reactivated.');
        }
        
        // Check if user has permission (only Prefect of Discipline, NOT Admin)
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        $isStaff = $currentUser && (int) $currentUser->role === 2;
        
        // Admins cannot reactivate accounts, only view
        if ($isAdmin) {
            return back()->with('error', 'Unauthorized: Only staff with "Prefect of Discipline" designation can reactivate student accounts. Admins can only view student details.');
        }
        
        if (!$isStaff) {
            return back()->with('error', 'Unauthorized: You do not have permission to reactivate accounts.');
        }
        
        // Check if current user is Prefect of Discipline
        $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($currentUser->email))])->first();
        $userDesignation = $currentUser->designation
            ?? optional($currentUser->staffProfile)->designation
            ?? ($staffRecord ? $staffRecord->designation : null);
        
        if (!$userDesignation || strcasecmp($userDesignation, 'Prefect of Discipline') !== 0) {
            return back()->with('error', 'Unauthorized: Only Prefect of Discipline can reactivate student accounts.');
        }
        
        $user->update([
            'suspended' => false,
            'suspension_reason' => null, // Clear suspension reason when reactivating
        ]);
        
        return back()->with('success', 'Student account has been reactivated successfully.');
    }

    public function events(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        
        // Check if user is OSA Staff
        $isOSAStaff = false;
        if ($user && (int) $user->role === 2) {
            // Try to find staff record by email (case-insensitive)
            $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            $normalizedDesignation = trim($userDesignation ?? '');
            $isOSAStaff = strcasecmp($normalizedDesignation, 'OSA Staff') === 0;
        }
        
        // Only allow Admin (role 4) or OSA Staff (role 2 with designation "OSA Staff")
        if (!$isAdmin && !$isOSAStaff) {
            // Redirect to designated dashboard
            if ($user && (int) $user->role === 2) {
                $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
                $userDesignation = $user->designation
                    ?? optional($user->staffProfile)->designation
                    ?? ($staffRecord ? $staffRecord->designation : null);
                
                if ($userDesignation) {
                    return redirect()->route('admin.staff.dashboard.designation', ['designation' => $userDesignation])
                        ->with('error', 'You do not have access to this page.');
                }
            }
            return redirect()->route('admin.staff.dashboard')
                ->with('error', 'You do not have access to this page.');
        }
        
        // Get all staff user IDs (role 2)
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        
        // Get all admin user IDs (role 4)
        $adminUserIds = \App\Models\User::where('role', 4)->pluck('id');
        
        // Base query for staff-created events
        $baseQuery = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        // Base query for admin-created events
        $adminBaseQuery = \App\Models\Event::whereIn('created_by', $adminUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        // Apply filters if provided
        $filteredQuery = clone $baseQuery;
        
        if ($request->filled('search')) {
            $search = $request->search;
            $filteredQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('description')) {
            $filteredQuery->where('description', $request->description);
        }
        
        if ($request->filled('organization_id')) {
            $filteredQuery->where('organization_id', $request->organization_id);
        }
        
        // Apply filters to admin query as well
        $adminFilteredQuery = clone $adminBaseQuery;
        if ($request->filled('search')) {
            $search = $request->search;
            $adminFilteredQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('description')) {
            $adminFilteredQuery->where('description', $request->description);
        }
        if ($request->filled('organization_id')) {
            $adminFilteredQuery->where('organization_id', $request->organization_id);
        }
        
        // 1. Pending Events - events created by staff but still need approval
        $pendingEvents = (clone $filteredQuery)
            ->where('status', 'pending')
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Admin-created pending events
        $adminPendingEvents = (clone $adminFilteredQuery)
            ->where('status', 'pending')
            ->orderBy('start_time', 'asc')
            ->get();
        
        // 2. Current Events - events happening on the current date
        $today = \Carbon\Carbon::today();
        $currentEvents = (clone $filteredQuery)
            ->where('status', 'approved')
            // Event is happening today if today's date falls between start_date and end_date
            ->whereDate('start_time', '<=', $today)
            ->whereDate('end_time', '>=', $today)
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Admin-created current events
        $adminCurrentEvents = (clone $adminFilteredQuery)
            ->where('status', 'approved')
            // Event is happening today if today's date falls between start_date and end_date
            ->whereDate('start_time', '<=', $today)
            ->whereDate('end_time', '>=', $today)
            ->orderBy('start_time', 'asc')
            ->get();
        
        // 3. Upcoming Events - approved events, arranged by date (soonest first)
        $upcomingEvents = (clone $filteredQuery)
            ->where('status', 'approved')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();
        
        // Admin-created upcoming events
        $adminUpcomingEvents = (clone $adminFilteredQuery)
            ->where('status', 'approved')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->get();
        
        // 4. Most Recent Events - events that were conducted most recently
        // Get events that have concluded (end_time < now()), ordered by most recent first
        $mostRecentEvents = (clone $filteredQuery)
            ->where('end_time', '<', now())
            ->whereNotNull('end_time')
            ->orderBy('end_time', 'desc')
            ->get();
        
        // Admin-created recent events
        $adminRecentEvents = (clone $adminFilteredQuery)
            ->where('end_time', '<', now())
            ->whereNotNull('end_time')
            ->orderBy('end_time', 'desc')
            ->get();
        
        // 5. Created Events - categorized into approved and declined
        $approvedCreatedEvents = (clone $filteredQuery)
            ->where('status', 'approved')
            ->orderBy('start_time', 'desc')
            ->get();
        
        $declinedCreatedEvents = (clone $filteredQuery)
            ->where('status', 'declined')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Admin-created events (approved and declined)
        $adminApprovedCreatedEvents = (clone $adminFilteredQuery)
            ->where('status', 'approved')
            ->orderBy('start_time', 'desc')
            ->get();
        
        $adminDeclinedCreatedEvents = (clone $adminFilteredQuery)
            ->where('status', 'declined')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $organizations = \App\Models\Organization::orderBy('name')->get();
        
        // Get descriptions from staff-created events only
        $descriptions = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->distinct()
            ->pluck('description')
            ->filter()
            ->sort();
        
        return view('admin.events', compact(
            'pendingEvents',
            'adminPendingEvents',
            'currentEvents',
            'adminCurrentEvents',
            'upcomingEvents',
            'adminUpcomingEvents',
            'mostRecentEvents',
            'adminRecentEvents',
            'approvedCreatedEvents',
            'declinedCreatedEvents',
            'adminApprovedCreatedEvents',
            'adminDeclinedCreatedEvents',
            'organizations',
            'descriptions',
            'isAdmin'
        ));
    }

    /**
     * Helper method to check if user is Admin or OSA Staff
     * Returns array with ['isAdmin' => bool, 'isOSAStaff' => bool, 'hasAccess' => bool]
     */
    private function checkAdminOrOSAStaffAccess()
    {
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        
        // Check if user is OSA Staff
        $isOSAStaff = false;
        if ($user && (int) $user->role === 2) {
            // Try to find staff record by email (case-insensitive)
            $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            $normalizedDesignation = trim($userDesignation ?? '');
            $isOSAStaff = strcasecmp($normalizedDesignation, 'OSA Staff') === 0;
        }
        
        return [
            'isAdmin' => $isAdmin,
            'isOSAStaff' => $isOSAStaff,
            'hasAccess' => $isAdmin || $isOSAStaff,
            'user' => $user
        ];
    }
    
    /**
     * Helper method to redirect non-authorized users to their designated dashboard
     */
    private function redirectToDesignatedDashboard($user)
    {
        if ($user && (int) $user->role === 2) {
            $staffRecord = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            if ($userDesignation) {
                return redirect()->route('admin.staff.dashboard.designation', ['designation' => $userDesignation])
                    ->with('error', 'You do not have access to this page.');
            }
        }
        return redirect()->route('admin.staff.dashboard')
            ->with('error', 'You do not have access to this page.');
    }
    
    /**
     * Helper method to build filtered query
     */
    private function buildFilteredQuery(Request $request)
    {
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        
        $query = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        if ($request->filled('description')) {
            $query->where('description', $request->description);
        }
        
        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        
        return $query;
    }

    public function pendingEvents(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $isAdmin = $access['isAdmin'];
        $query = $this->buildFilteredQuery($request);
        
        $events = $query->where('status', 'pending')
            ->orderBy('start_time', 'asc')
            ->paginate(15)
            ->withQueryString();
        
        // Get admin-created pending events
        $adminUserIds = \App\Models\User::where('role', 4)->pluck('id');
        $adminQuery = \App\Models\Event::whereIn('created_by', $adminUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $adminQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('description')) {
            $adminQuery->where('description', $request->description);
        }
        if ($request->filled('organization_id')) {
            $adminQuery->where('organization_id', $request->organization_id);
        }
        
        $adminEvents = $adminQuery->where('status', 'pending')
            ->orderBy('start_time', 'asc')
            ->paginate(15, ['*'], 'admin_page')
            ->withQueryString();
        
        $organizations = \App\Models\Organization::orderBy('name')->get();
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        $descriptions = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->where('status', 'pending')
            ->distinct()
            ->pluck('description')
            ->filter()
            ->sort();
        
        return view('admin.events.pending-events', compact('events', 'adminEvents', 'organizations', 'descriptions', 'isAdmin'));
    }

    public function currentEvents(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $isAdmin = $access['isAdmin'];
        $query = $this->buildFilteredQuery($request);
        
        // Get today's date
        $today = \Carbon\Carbon::today();
        
        $events = $query->where('status', 'approved')
            // Event is happening today if today's date falls between start_date and end_date
            ->whereDate('start_time', '<=', $today)
            ->whereDate('end_time', '>=', $today)
            ->orderBy('start_time', 'asc')
            ->paginate(15)
            ->withQueryString();
        
        // Get admin-created current events
        $adminUserIds = \App\Models\User::where('role', 4)->pluck('id');
        $adminQuery = \App\Models\Event::whereIn('created_by', $adminUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $adminQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('description')) {
            $adminQuery->where('description', $request->description);
        }
        if ($request->filled('organization_id')) {
            $adminQuery->where('organization_id', $request->organization_id);
        }
        
        $adminEvents = $adminQuery->where('status', 'approved')
            // Event is happening today if today's date falls between start_date and end_date
            ->whereDate('start_time', '<=', $today)
            ->whereDate('end_time', '>=', $today)
            ->orderBy('start_time', 'asc')
            ->paginate(15, ['*'], 'admin_page')
            ->withQueryString();
        
        $organizations = \App\Models\Organization::orderBy('name')->get();
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        $descriptions = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->where('status', 'approved')
            ->distinct()
            ->pluck('description')
            ->filter()
            ->sort();
        
        return view('admin.events.current-events', compact('events', 'adminEvents', 'organizations', 'descriptions', 'isAdmin'));
    }

    public function upcomingEvents(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $isAdmin = $access['isAdmin'];
        $query = $this->buildFilteredQuery($request);
        
        $events = $query->where('status', 'approved')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->paginate(15)
            ->withQueryString();
        
        // Get admin-created upcoming events
        $adminUserIds = \App\Models\User::where('role', 4)->pluck('id');
        $adminQuery = \App\Models\Event::whereIn('created_by', $adminUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $adminQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('description')) {
            $adminQuery->where('description', $request->description);
        }
        if ($request->filled('organization_id')) {
            $adminQuery->where('organization_id', $request->organization_id);
        }
        
        $adminEvents = $adminQuery->where('status', 'approved')
            ->where('start_time', '>', now())
            ->orderBy('start_time', 'asc')
            ->paginate(15, ['*'], 'admin_page')
            ->withQueryString();
        
        $organizations = \App\Models\Organization::orderBy('name')->get();
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        $descriptions = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->where('status', 'approved')
            ->distinct()
            ->pluck('description')
            ->filter()
            ->sort();
        
        return view('admin.events.upcoming-events', compact('events', 'adminEvents', 'organizations', 'descriptions', 'isAdmin'));
    }

    public function recentEvents(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $isAdmin = $access['isAdmin'];
        $query = $this->buildFilteredQuery($request);
        
        $events = $query->where('end_time', '<', now())
            ->whereNotNull('end_time')
            ->orderBy('end_time', 'desc')
            ->paginate(15)
            ->withQueryString();
        
        // Get admin-created recent events
        $adminUserIds = \App\Models\User::where('role', 4)->pluck('id');
        $adminQuery = \App\Models\Event::whereIn('created_by', $adminUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $adminQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('description')) {
            $adminQuery->where('description', $request->description);
        }
        if ($request->filled('organization_id')) {
            $adminQuery->where('organization_id', $request->organization_id);
        }
        
        $adminEvents = $adminQuery->where('end_time', '<', now())
            ->whereNotNull('end_time')
            ->orderBy('end_time', 'desc')
            ->paginate(15, ['*'], 'admin_page')
            ->withQueryString();
        
        $organizations = \App\Models\Organization::orderBy('name')->get();
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        $descriptions = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->whereNotNull('end_time')
            ->where('end_time', '<', now())
            ->distinct()
            ->pluck('description')
            ->filter()
            ->sort();
        
        return view('admin.events.recent-events', compact('events', 'adminEvents', 'organizations', 'descriptions', 'isAdmin'));
    }

    public function createdEvents(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $isAdmin = $access['isAdmin'];
        $query = $this->buildFilteredQuery($request);
        
        $approvedQuery = clone $query;
        $declinedQuery = clone $query;
        
        $approvedEvents = $approvedQuery->where('status', 'approved')
            ->orderBy('start_time', 'desc')
            ->paginate(15, ['*'], 'approved_page')
            ->withQueryString();
        
        $declinedEvents = $declinedQuery->where('status', 'declined')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'declined_page')
            ->withQueryString();
        
        // Get admin-created events
        $adminUserIds = \App\Models\User::where('role', 4)->pluck('id');
        $adminQuery = \App\Models\Event::whereIn('created_by', $adminUserIds)
            ->with(['creator', 'organization', 'requirements', 'participants']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $adminQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        if ($request->filled('description')) {
            $adminQuery->where('description', $request->description);
        }
        if ($request->filled('organization_id')) {
            $adminQuery->where('organization_id', $request->organization_id);
        }
        
        $adminApprovedQuery = clone $adminQuery;
        $adminDeclinedQuery = clone $adminQuery;
        
        $adminApprovedEvents = $adminApprovedQuery->where('status', 'approved')
            ->orderBy('start_time', 'desc')
            ->paginate(15, ['*'], 'admin_approved_page')
            ->withQueryString();
        
        $adminDeclinedEvents = $adminDeclinedQuery->where('status', 'declined')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'admin_declined_page')
            ->withQueryString();
        
        $organizations = \App\Models\Organization::orderBy('name')->get();
        $staffUserIds = \App\Models\User::where('role', 2)->pluck('id');
        $descriptions = \App\Models\Event::whereIn('created_by', $staffUserIds)
            ->distinct()
            ->pluck('description')
            ->filter()
            ->sort();
        
        return view('admin.events.created-events', compact('approvedEvents', 'declinedEvents', 'adminApprovedEvents', 'adminDeclinedEvents', 'organizations', 'descriptions', 'isAdmin'));
    }

    public function createEvent()
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        // Simple form for admin to add events directly as approved
        return view('admin.events-create');
    }

    public function showEvent($id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $event = \App\Models\Event::with(['creator', 'organization', 'requirements.uploader'])
            ->findOrFail($id);
        
        // Default requirements list
        $defaultRequirements = [
            'Student Activity Request Form',
            'Program Flow',
            'Letters of Approval',
            'Financial Report',
            'Accomplishment Report'
        ];
        
        // Get forwarded requirements for this event
        $forwardedRequirements = $event->requirements()->orderBy('created_at', 'asc')->get();
        
        // Check if event is declined
        $isDeclined = $event->status === 'declined';
        
        return view('admin.events.show', compact('event', 'defaultRequirements', 'forwardedRequirements', 'isDeclined'));
    }

    /**
     * Notify organization about missing requirements (public method for manual notification)
     */
    public function notifyOrganizationRequirements(Request $request, $id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $event = \App\Models\Event::with('organization')->findOrFail($id);
        
        // Prevent notifying if event is declined
        if ($event->status === 'declined') {
            return back()->with('error', 'Cannot notify about missing requirements for a declined event.');
        }

        $this->notifyMissingRequirements($event);

        return back()->with('success', 'Notification sent to organization about missing requirements.');
    }

    public function editEvent($id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $event = \App\Models\Event::findOrFail($id);
        
        // Prevent editing if event is declined
        if ($event->status === 'declined') {
            return redirect()->route('admin.events.show', $event->id)
                ->with('error', 'Cannot edit a declined event. The event is considered closed.');
        }
        
        // Allow admins to edit any admin-created event, or allow creator to edit their own event
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $eventCreator = \App\Models\User::find($event->created_by);
        $isAdminCreatedEvent = $eventCreator && (int) $eventCreator->role === 4;
        
        if ($event->created_by !== auth()->id() && !($isAdmin && $isAdminCreatedEvent)) {
            abort(403, 'Unauthorized access.');
        }
        $organizations = \App\Models\Organization::orderBy('name')->get();
        return view('admin.events-edit', compact('event', 'organizations'));
    }

    public function storeEvent(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'required_student_participation' => 'nullable|boolean',
            'points' => 'nullable|integer|min:0',
            'event_files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls,csv,txt|max:10240', // 10MB per file
        ]);

        $description = $request->description;

        // Normalize time values to HH:MM:SS for MySQL strict mode
        $startTime = $request->start_time;
        $endTime = $request->end_time;
        if (is_string($startTime) && preg_match('/^\d{2}:\d{2}$/', $startTime)) {
            $startTime .= ':00';
        }
        if (is_string($endTime) && preg_match('/^\d{2}:\d{2}$/', $endTime)) {
            $endTime .= ':00';
        }

        // Build start/end as DATETIME (use date + time)
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $startDateTime = $startDate . ' ' . ($startTime ?: '00:00:00');
        $endDateTime = $endDate . ' ' . ($endTime ?: ($startTime ?: '23:59:59'));

        // Check for duplicate events (same name with overlapping dates)
        $duplicates = \App\Models\Event::where('name', $request->name)
            ->where(function($query) use ($startDateTime, $endDateTime) {
                // Check for overlapping date ranges - two date ranges overlap if:
                // start1 <= end2 AND start2 <= end1
                $query->where('start_time', '<=', $endDateTime)
                      ->where('end_time', '>=', $startDateTime);
            })
            ->get();

        // If duplicates found, show selection page
        if ($duplicates->isNotEmpty()) {
            // Store the new event data in session for later use
            session([
                'pending_event' => [
                    'name' => $request->name,
                    'description' => $description,
                    'start_time' => $startDateTime,
                    'end_time' => $endDateTime,
                    'location' => $request->location,
                    'required_student_participation' => $request->has('required_student_participation') ? (bool) $request->required_student_participation : false,
                    'points' => $request->has('points') && $request->points !== '' ? (int) $request->points : null,
                ]
            ]);
            
            return view('admin.events-duplicate', [
                'duplicates' => $duplicates,
                'newEvent' => [
                    'name' => $request->name,
                    'description' => $description,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => $request->location,
                    'start_datetime' => $startDateTime,
                    'end_datetime' => $endDateTime,
                ]
            ]);
        }

        $requiredStudentParticipation = $request->has('required_student_participation') ? (bool) $request->required_student_participation : false;
        
        $event = new \App\Models\Event();
        $event->fill([
            'name' => $request->name,
            'description' => $description,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'location' => $request->location,
            'qr_code_path' => '',
            'status' => 'approved',
            'created_by' => auth()->id(),
            'required_student_participation' => $requiredStudentParticipation,
            'points' => $request->has('points') && $request->points !== '' ? (int) $request->points : null,
        ]);
        $event->save();
        
        // Generate QR code for the event only if Required Student Participation is ON
        if ($requiredStudentParticipation) {
            try {
                $payload = [
                    'event_id' => $event->id,
                    'name' => $event->name,
                    'start_date' => \Carbon\Carbon::parse($event->start_time)->format('Y-m-d'),
                    'end_date' => \Carbon\Carbon::parse($event->end_time)->format('Y-m-d'),
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'location' => $event->location,
                    'created_at' => now()->toIso8601String(),
                ];
                $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate(json_encode($payload));
                $path = 'qr/events/'.$event->id.'.svg';
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $svg);
                $event->qr_code_path = $path;
                $event->save();
            } catch (\Throwable $e) {
                // Non-fatal: continue without blocking event creation
            }
        }

        // Handle file uploads
        if ($request->hasFile('event_files')) {
            foreach ($request->file('event_files') as $file) {
                // Sanitize filename
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
                
                // Store file in events/{event_id}/files/
                $storagePath = 'events/' . $event->id . '/files';
                $path = $file->storeAs($storagePath, $filename, 'public');
                
                // Determine file type from extension
                $extension = strtolower($file->getClientOriginalExtension());
                $fileType = 'document';
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $fileType = 'image';
                } elseif ($extension === 'pdf') {
                    $fileType = 'pdf';
                } elseif (in_array($extension, ['xlsx', 'xls', 'csv'])) {
                    $fileType = 'spreadsheet';
                }
                
                // Create database record
                \App\Models\EventFile::create([
                    'event_id' => $event->id,
                    'uploaded_by' => auth()->id(),
                    'file_name' => $originalName,
                    'file_path' => $path,
                    'file_type' => $fileType,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return redirect()->route('admin.events.create')->with('success', 'Event created and approved successfully.');
    }

    /**
     * Handle duplicate event resolution - user chooses which event to keep
     */
    public function resolveDuplicate(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $request->validate([
            'action' => 'required|in:keep_new,keep_existing,keep_both',
            'existing_event_ids' => 'required_if:action,keep_existing|array',
            'existing_event_ids.*' => 'exists:events,id',
        ]);

        $pendingEvent = session('pending_event');
        
        if (!$pendingEvent) {
            return redirect()->route('admin.events.create')
                ->with('error', 'Session expired. Please try creating the event again.');
        }

        if ($request->action === 'keep_new') {
            // Delete all duplicate existing events
            $duplicateIds = $request->input('existing_event_ids', []);
            if (!empty($duplicateIds)) {
                \App\Models\Event::whereIn('id', $duplicateIds)->delete();
            }
            
            // Create the new event
            $requiredStudentParticipation = $pendingEvent['required_student_participation'] ?? false;
            $event = new \App\Models\Event();
            $event->fill([
                'name' => $pendingEvent['name'],
                'description' => $pendingEvent['description'],
                'start_time' => $pendingEvent['start_time'],
                'end_time' => $pendingEvent['end_time'],
                'location' => $pendingEvent['location'],
                'qr_code_path' => '',
                'status' => 'approved',
                'created_by' => auth()->id(),
                'required_student_participation' => $requiredStudentParticipation,
            ]);
            $event->save();
            
            // Generate QR code only if Required Student Participation is ON
            if ($requiredStudentParticipation) {
                try {
                    $payload = [
                        'event_id' => $event->id,
                        'name' => $event->name,
                        'start_date' => \Carbon\Carbon::parse($event->start_time)->format('Y-m-d'),
                        'end_date' => \Carbon\Carbon::parse($event->end_time)->format('Y-m-d'),
                        'start_time' => $event->start_time,
                        'end_time' => $event->end_time,
                        'location' => $event->location,
                        'created_at' => now()->toIso8601String(),
                    ];
                    $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate(json_encode($payload));
                    $path = 'qr/events/'.$event->id.'.svg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $svg);
                    $event->qr_code_path = $path;
                    $event->save();
                } catch (\Throwable $e) {
                    // Non-fatal
                }
            }
            
            session()->forget('pending_event');
            return redirect()->route('admin.events.create')
                ->with('success', 'Event created successfully. Duplicate events were removed.');
        }
        
        if ($request->action === 'keep_existing') {
            // Don't create new event, just keep existing ones
            session()->forget('pending_event');
            return redirect()->route('admin.events.create')
                ->with('info', 'Event creation cancelled. Existing events were kept.');
        }
        
        if ($request->action === 'keep_both') {
            // Create new event even though duplicates exist
            $requiredStudentParticipation = $pendingEvent['required_student_participation'] ?? false;
            $event = new \App\Models\Event();
            $event->fill([
                'name' => $pendingEvent['name'],
                'description' => $pendingEvent['description'],
                'start_time' => $pendingEvent['start_time'],
                'end_time' => $pendingEvent['end_time'],
                'location' => $pendingEvent['location'],
                'qr_code_path' => '',
                'status' => 'approved',
                'created_by' => auth()->id(),
                'required_student_participation' => $requiredStudentParticipation,
            ]);
            $event->save();
            
            // Generate QR code only if Required Student Participation is ON
            if ($requiredStudentParticipation) {
                try {
                    $payload = [
                        'event_id' => $event->id,
                        'name' => $event->name,
                        'start_date' => \Carbon\Carbon::parse($event->start_time)->format('Y-m-d'),
                        'end_date' => \Carbon\Carbon::parse($event->end_time)->format('Y-m-d'),
                        'start_time' => $event->start_time,
                        'end_time' => $event->end_time,
                        'location' => $event->location,
                        'created_at' => now()->toIso8601String(),
                    ];
                    $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate(json_encode($payload));
                    $path = 'qr/events/'.$event->id.'.svg';
                    \Illuminate\Support\Facades\Storage::disk('public')->put($path, $svg);
                    $event->qr_code_path = $path;
                    $event->save();
                } catch (\Throwable $e) {
                    // Non-fatal
                }
            }
            
            session()->forget('pending_event');
            return redirect()->route('admin.events.create')
                ->with('success', 'Event created successfully. Both existing and new events are kept.');
        }

        return redirect()->route('admin.events.create')
            ->with('error', 'Invalid action selected.');
    }

    public function eventsHistory(Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }
        
        $isAdmin = $access['isAdmin'];
        $query = \App\Models\Event::with(['creator', 'requirements', 'participants']);
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('year_level')) {
            $query->whereHas('studentInformation', function ($q) use ($request) {
                $q->where('year_level', $request->year_level);
            });
        }
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $events = $query->orderBy('event_date', 'desc')->paginate(15);
        // Use cached reference data
        $departments = \App\Services\CacheService::getDepartments();
        $courses = \App\Services\CacheService::getCourses();
        return view('admin.events-history', compact('events', 'departments', 'courses'));
    }

    /**
     * Build organizational structure: Admin → Staff
     * Structure:
     * - Level 0: Admin (admin002)
     * - Level 1: OSA Staff (directly below admin)
     * - Level 2: Staff designations (Guidance Counselor, Admission Services Officer, Prefect of Discipline, Nurse, Librarian, Carriers Management Officer) - directly connected to admin
     * - Level 3: Student Org. Moderators - directly connected to admin
     */
    private function buildAdminStaffOrgStructure()
    {
        $nodes = [];
        $edges = [];

        // Get admin002 as the OSA Head (Admin), or fallback to first admin
        $adminHead = \App\Models\User::where('role', 4)
            ->where('user_id', 'admin002')
            ->first();
        
        // If admin002 doesn't exist, get the first admin user
        if (!$adminHead) {
            $adminHead = \App\Models\User::where('role', 4)->first();
        }
        
        // Get all staff (Staff model) with relationships, sorted alphabetically
        $allStaff = \App\Models\Staff::with(['organizations', 'department', 'admin'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        // Categorize staff by designation
        $osaStaff = $allStaff->filter(function($staff) {
            return strcasecmp($staff->designation ?? '', 'OSA Staff') === 0;
        });
        
        // Staff with Designations (excluding OSA Staff and Student Org. Moderator)
        $designationStaff = $allStaff->filter(function($staff) {
            $designation = $staff->designation ?? '';
            // Normalize designation to handle both British and American spellings
            $normalizedDesignation = strtolower($designation);
            if ($normalizedDesignation === 'guidance counsellor') {
                $normalizedDesignation = 'guidance counselor';
            }
            // Exclude OSA Staff and Student Org. Moderator
            if (strcasecmp($designation, 'OSA Staff') === 0 || 
                strcasecmp($designation, 'Student Org. Moderator') === 0) {
                return false;
            }
            return in_array($normalizedDesignation, [
                'guidance counselor',
                'guidance counsellor', // Backward compatibility
                'admission services officer',
                'prefect of discipline',
                'nurse',
                'librarian',
                'carriers management officer'
            ], true);
        });

        // Add OSA Head (Admin) as top-level node (Level 0)
        if ($adminHead) {
            $adminName = trim(($adminHead->first_name ?? '') . ' ' . ($adminHead->last_name ?? ''));
            if (empty($adminName)) {
                $adminName = 'OSA Head';
            }
            $nodes[] = [
                'id' => 'admin-' . $adminHead->id,
                'label' => $adminName . '\n(OSA Head - Admin)',
                'level' => 0,
                'group' => 'admin',
                'title' => $adminName . ' - OSA Head (Admin)'
            ];
            $adminNodeId = 'admin-' . $adminHead->id;
        } else {
            // Fallback: generic OSA Head node
            $nodes[] = [
                'id' => 'admin-root',
                'label' => 'OSA Head\n(Admin)',
                'level' => 0,
                'group' => 'admin',
                'title' => 'OSA Head (Admin)'
            ];
            $adminNodeId = 'admin-root';
        }
        
        // Helper function to create staff node
        $createStaffNode = function($staff, $level) use (&$nodes, &$edges, $adminNodeId) {
            $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
            if (empty($staffName)) {
                return null;
            }
            
            $designation = $staff->designation ?? 'Staff';
            $department = $staff->department ? $staff->department->name : 'No Department';
            
            // Get all affiliated organizations with IDs for clickable links
            $orgs = [];
            if ($staff->organizations && $staff->organizations->count() > 0) {
                foreach ($staff->organizations as $org) {
                    $orgs[] = [
                        'id' => $org->id,
                        'name' => $org->name
                    ];
                }
            }
            if ($staff->organization && $staff->organization->name) {
                $orgExists = false;
                foreach ($orgs as $org) {
                    if ($org['id'] == $staff->organization->id) {
                        $orgExists = true;
                        break;
                    }
                }
                if (!$orgExists) {
                    $orgs[] = [
                        'id' => $staff->organization->id,
                        'name' => $staff->organization->name
                    ];
                }
            }
            
            // Build organization names string for label
            $orgNamesString = !empty($orgs) ? implode(', ', array_column($orgs, 'name')) : 'No Organization';
            
            // Get image URL - use same pattern as other views
            $imageUrl = null;
            if ($staff->image) {
                // Use Storage::url() directly like other views do
                $imageUrl = \Illuminate\Support\Facades\Storage::url($staff->image);
            }
            
            // Build label with name and designation (will be styled with HTML)
            $label = $staffName . '\n' . $designation;
            
            // Build detailed title with all information for tooltip
            $title = $staffName . '\n\n' . 
                     'Designation: ' . $designation . '\n' . 
                     'Department: ' . $department . '\n' . 
                     'Organizations: ' . $orgNamesString;
            
            $nodeData = [
                'id' => 'staff-' . $staff->id,
                'label' => $label,
                'level' => $level,
                'group' => 'staff',
                'title' => $title,
                'staff_id' => $staff->id,
                'organizations' => $orgs, // Store org data for clickable links
                'image' => $imageUrl, // Always store image URL (even if null)
                'department' => $department // Store department for display
            ];
            
            $nodes[] = $nodeData;
            
            // Connect directly to OSA Head (Admin)
            $edges[] = [
                'from' => $adminNodeId,
                'to' => 'staff-' . $staff->id
            ];
            
            return 'staff-' . $staff->id;
        };
        
        // Helper function to create assistant node
        $createAssistantNode = function($assistant, $staffNodeId, $position = null) use (&$nodes, &$edges) {
            $assistantName = trim(($assistant->first_name ?? '') . ' ' . ($assistant->last_name ?? ''));
            if (empty($assistantName)) {
                return null;
            }
            
            $assistantPosition = $position ?? 'Student Leader';
            $department = $assistant->department ? $assistant->department->name : 'No Department';
            
            // Get image URL
            $imageUrl = null;
            if ($assistant->image) {
                // Ensure image path is in staff-image directory (consistent with admin and staff)
                $imagePath = $assistant->image;
                // Remove any leading slashes or existing directory paths
                $imagePath = ltrim($imagePath, '/');
                if (strpos($imagePath, 'staff-image/') === false) {
                    $imagePath = 'staff-image/' . basename($imagePath);
                }
                $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            }
            
            // Build label
            $label = $assistantName . '\n' . $assistantPosition;
            
            // Build detailed title
            $title = $assistantName . '\n\n' . 
                     'Position: ' . $assistantPosition . '\n' . 
                     'Department: ' . $department;
            
            $nodeData = [
                'id' => 'assistant-' . $assistant->id,
                'label' => $label,
                'level' => 2,
                'group' => 'assistant',
                'title' => $title,
                'assistant_id' => $assistant->id,
                'image' => $imageUrl,
                'department' => $department
            ];
            
            $nodes[] = $nodeData;
            
            // Connect to staff supervisor
            $edges[] = [
                'from' => $staffNodeId,
                'to' => 'assistant-' . $assistant->id
            ];
            
            return 'assistant-' . $assistant->id;
        };
        
        // Level 1: OSA Staff (directly under OSA Head)
        foreach ($osaStaff as $staff) {
            $staffNodeId = $createStaffNode($staff, 1);
        }
        
        // Level 1: Staff with Designations (directly under OSA Head)
        // Also fetch and add their Student Leaders at Level 2
        foreach ($designationStaff as $staff) {
            $staffNodeId = $createStaffNode($staff, 1);
            
            // Find the User record for this staff member (by email)
            $staffUser = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower(trim($staff->email))])
                ->where('role', 2)
                ->first();
            
            if ($staffUser && $staffNodeId) {
                // Get all active assistant assignments for this staff member
                $assistantAssignments = \App\Models\AssistantAssignment::where('supervisor_id', $staffUser->id)
                    ->where('is_active', true)
                    ->with(['user' => function($query) {
                        $query->with('department');
                    }])
                    ->get();
                
                // Add student leader nodes at Level 2
                foreach ($assistantAssignments as $assignment) {
                    if ($assignment->user) {
                        $createAssistantNode($assignment->user, $staffNodeId, $assignment->position);
                    }
                }
            }
        }

        // Ensure we always have at least the admin node
        if (empty($nodes)) {
            // Fallback: create a generic admin node if nothing was created
            $nodes[] = [
                'id' => 'admin-root',
                'label' => 'OSA Head\n(Admin)',
                'level' => 0,
                'group' => 'admin',
                'title' => 'OSA Head (Admin)'
            ];
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }

    /**
     * Build structured data for plain HTML display of organizational structure
     */
    private function buildAdminStaffOrgStructureData()
    {
        $data = [
            'admin' => null,
            'osaStaff' => [],
            'designationStaff' => []
        ];

        // Get admin002 as the OSA Head (Admin), or fallback to first admin
        $adminHead = \App\Models\User::where('role', 4)
            ->where('user_id', 'admin002')
            ->first();
        
        if (!$adminHead) {
            $adminHead = \App\Models\User::where('role', 4)->first();
        }

        if ($adminHead) {
            $adminName = trim(($adminHead->first_name ?? '') . ' ' . ($adminHead->last_name ?? ''));
            if (empty($adminName)) {
                $adminName = 'OSA Head';
            }
            
            $adminImage = null;
            if ($adminHead->image) {
                // Ensure image path is in staff-image directory
                $imagePath = $adminHead->image;
                // Remove any leading slashes or existing directory paths
                $imagePath = ltrim($imagePath, '/');
                if (strpos($imagePath, 'staff-image/') === false) {
                    $imagePath = 'staff-image/' . basename($imagePath);
                }
                $adminImage = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            }
            
            $data['admin'] = [
                'id' => $adminHead->id,
                'name' => $adminName,
                'designation' => 'OSA Head (Admin)',
                'image' => $adminImage
            ];
        } else {
            $data['admin'] = [
                'id' => null,
                'name' => 'OSA Head',
                'designation' => 'Admin',
                'image' => null
            ];
        }

        // Get all staff (Staff model) with relationships
        $allStaff = \App\Models\Staff::with(['organizations', 'department', 'admin'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Categorize staff by designation
        $osaStaff = $allStaff->filter(function($staff) {
            return strcasecmp($staff->designation ?? '', 'OSA Staff') === 0;
        });

        // Staff with Designations (excluding OSA Staff and Student Org. Moderator)
        // Use the same filtering logic as buildAdminStaffOrgStructure() for consistency
        $designationStaff = $allStaff->filter(function($staff) {
            $designation = $staff->designation ?? '';
            // Normalize designation to handle both British and American spellings
            $normalizedDesignation = strtolower($designation);
            if ($normalizedDesignation === 'guidance counsellor') {
                $normalizedDesignation = 'guidance counselor';
            }
            // Exclude OSA Staff and Student Org. Moderator
            if (strcasecmp($designation, 'OSA Staff') === 0 || 
                strcasecmp($designation, 'Student Org. Moderator') === 0) {
                return false;
            }
            return in_array($normalizedDesignation, [
                'guidance counselor',
                'guidance counsellor', // Backward compatibility
                'admission services officer',
                'prefect of discipline',
                'nurse',
                'librarian',
                'carriers management officer'
            ], true);
        });

        // Build OSA Staff data (including their assistants)
        foreach ($osaStaff as $staff) {
            $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
            if (empty($staffName)) {
                continue;
            }

            $imageUrl = null;
            if ($staff->image) {
                // Ensure image path is in staff-image directory
                $imagePath = $staff->image;
                // Remove any leading slashes or existing directory paths
                $imagePath = ltrim($imagePath, '/');
                if (strpos($imagePath, 'staff-image/') === false) {
                    $imagePath = 'staff-image/' . basename($imagePath);
                }
                $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            }

            // Find the User record for this staff member
            $staffUser = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower(trim($staff->email))])
                ->where('role', 2)
                ->first();

            $assistants = [];
            $assistantIds = collect(); // Track assistant IDs to avoid duplicates
            
            if ($staffUser) {
                // Get assistants from AssistantAssignment table
                $assistantAssignments = \App\Models\AssistantAssignment::where('supervisor_id', $staffUser->id)
                    ->where('active', true)
                    ->with(['user' => function($query) {
                        $query->with('department');
                    }, 'organization'])
                    ->get();

                foreach ($assistantAssignments as $assignment) {
                    if ($assignment->user) {
                        $assistantIds->push($assignment->user->id);
                        $assistantName = trim(($assignment->user->first_name ?? '') . ' ' . ($assignment->user->last_name ?? ''));
                        if (empty($assistantName)) {
                            continue;
                        }

                        $assistantImage = null;
                        if ($assignment->user->image) {
                            // Ensure image path is in staff-image directory (consistent with admin and staff)
                            $imagePath = $assignment->user->image;
                            // Remove any leading slashes or existing directory paths
                            $imagePath = ltrim($imagePath, '/');
                            if (strpos($imagePath, 'staff-image/') === false) {
                                $imagePath = 'staff-image/' . basename($imagePath);
                            }
                            $assistantImage = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                        }

                        $assistants[] = [
                            'id' => $assignment->user->id,
                            'name' => $assistantName,
                            'position' => $assignment->position ?? 'Student Leader',
                            'organization_name' => optional($assignment->organization)->name ?? optional($assignment->user->organization)->name ?? 'N/A',
                            'organization_id' => $assignment->organization_id ?? ($assignment->user->organization_id ?? null),
                            'image' => $assistantImage
                        ];
                    }
                }
                
                // Also get legacy assistants (role 3 with supervisor_id on User model)
                $legacyAssistants = \App\Models\User::where('role', 3)
                    ->where('supervisor_id', $staffUser->id)
                    ->whereNotIn('id', $assistantIds->toArray())
                    ->with(['department', 'organization', 'otherOrganizations'])
                    ->get();
                
                foreach ($legacyAssistants as $legacyAssistant) {
                    $assistantName = trim(($legacyAssistant->first_name ?? '') . ' ' . ($legacyAssistant->last_name ?? ''));
                    if (empty($assistantName)) {
                        continue;
                    }

                    $assistantImage = null;
                    if ($legacyAssistant->image) {
                        // Ensure image path is in staff-image directory
                        $imagePath = $legacyAssistant->image;
                        $imagePath = ltrim($imagePath, '/');
                        if (strpos($imagePath, 'staff-image/') === false) {
                            $imagePath = 'staff-image/' . basename($imagePath);
                        }
                        $assistantImage = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                    }

                    // Get organization name from primary or other organizations
                    $orgName = optional($legacyAssistant->organization)->name ?? 'N/A';
                    if ($orgName === 'N/A' && $legacyAssistant->otherOrganizations && $legacyAssistant->otherOrganizations->isNotEmpty()) {
                        $orgName = optional($legacyAssistant->otherOrganizations->first())->name ?? 'N/A';
                    }

                    $assistants[] = [
                        'id' => $legacyAssistant->id,
                        'name' => $assistantName,
                        'position' => $legacyAssistant->position ?? 'Student Leader',
                        'organization_name' => $orgName,
                        'organization_id' => $legacyAssistant->organization_id ?? (optional($legacyAssistant->otherOrganizations->first())->id ?? null),
                        'image' => $assistantImage
                    ];
                }
                
                // Group assistants by organization name
                $assistants = collect($assistants)->groupBy('organization_name')->map(function($group) {
                    return $group->values()->all();
                })->toArray();
            }
            
            // Get organizations assigned to this staff member
            $organizations = [];
            if ($staff->organizations && $staff->organizations->isNotEmpty()) {
                foreach ($staff->organizations as $org) {
                    $organizations[] = [
                        'id' => $org->id,
                        'name' => $org->name
                    ];
                }
            } elseif ($staff->organization_id) {
                // Fallback to single organization_id if organizations relationship is empty
                $org = \App\Models\Organization::find($staff->organization_id);
                if ($org) {
                    $organizations[] = [
                        'id' => $org->id,
                        'name' => $org->name
                    ];
                }
            }

            $data['osaStaff'][] = [
                'id' => $staff->id,
                'name' => $staffName,
                'designation' => $staff->designation ?? 'OSA Staff',
                'image' => $imageUrl,
                'organizations' => $organizations,
                'assistants' => $assistants
            ];
        }

        // Build Staff with Designations data (including their assistants)
        foreach ($designationStaff as $staff) {
            $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
            if (empty($staffName)) {
                continue;
            }

            $imageUrl = null;
            if ($staff->image) {
                // Ensure image path is in staff-image directory
                $imagePath = $staff->image;
                // Remove any leading slashes or existing directory paths
                $imagePath = ltrim($imagePath, '/');
                if (strpos($imagePath, 'staff-image/') === false) {
                    $imagePath = 'staff-image/' . basename($imagePath);
                }
                $imageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
            }

            // Find the User record for this staff member
            $staffUser = \App\Models\User::whereRaw('LOWER(email) = ?', [strtolower(trim($staff->email))])
                ->where('role', 2)
                ->first();

            $assistants = [];
            $assistantIds = collect(); // Track assistant IDs to avoid duplicates
            
            if ($staffUser) {
                // Get assistants from AssistantAssignment table
                $assistantAssignments = \App\Models\AssistantAssignment::where('supervisor_id', $staffUser->id)
                    ->where('active', true)
                    ->with(['user' => function($query) {
                        $query->with('department');
                    }, 'organization'])
                    ->get();

                foreach ($assistantAssignments as $assignment) {
                    if ($assignment->user) {
                        $assistantIds->push($assignment->user->id);
                        $assistantName = trim(($assignment->user->first_name ?? '') . ' ' . ($assignment->user->last_name ?? ''));
                        if (empty($assistantName)) {
                            continue;
                        }

                        $assistantImage = null;
                        if ($assignment->user->image) {
                            // Ensure image path is in staff-image directory (consistent with admin and staff)
                            $imagePath = $assignment->user->image;
                            // Remove any leading slashes or existing directory paths
                            $imagePath = ltrim($imagePath, '/');
                            if (strpos($imagePath, 'staff-image/') === false) {
                                $imagePath = 'staff-image/' . basename($imagePath);
                            }
                            $assistantImage = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                        }

                        $assistants[] = [
                            'id' => $assignment->user->id,
                            'name' => $assistantName,
                            'position' => $assignment->position ?? 'Student Leader',
                            'organization_name' => optional($assignment->organization)->name ?? optional($assignment->user->organization)->name ?? 'N/A',
                            'organization_id' => $assignment->organization_id ?? ($assignment->user->organization_id ?? null),
                            'image' => $assistantImage
                        ];
                    }
                }
                
                // Also get legacy assistants (role 3 with supervisor_id on User model)
                $legacyAssistants = \App\Models\User::where('role', 3)
                    ->where('supervisor_id', $staffUser->id)
                    ->whereNotIn('id', $assistantIds->toArray())
                    ->with(['department', 'organization', 'otherOrganizations'])
                    ->get();
                
                foreach ($legacyAssistants as $legacyAssistant) {
                    $assistantName = trim(($legacyAssistant->first_name ?? '') . ' ' . ($legacyAssistant->last_name ?? ''));
                    if (empty($assistantName)) {
                        continue;
                    }

                    $assistantImage = null;
                    if ($legacyAssistant->image) {
                        // Ensure image path is in staff-image directory
                        $imagePath = $legacyAssistant->image;
                        $imagePath = ltrim($imagePath, '/');
                        if (strpos($imagePath, 'staff-image/') === false) {
                            $imagePath = 'staff-image/' . basename($imagePath);
                        }
                        $assistantImage = \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath);
                    }

                    // Get organization name from primary or other organizations
                    $orgName = optional($legacyAssistant->organization)->name ?? 'N/A';
                    if ($orgName === 'N/A' && $legacyAssistant->otherOrganizations && $legacyAssistant->otherOrganizations->isNotEmpty()) {
                        $orgName = optional($legacyAssistant->otherOrganizations->first())->name ?? 'N/A';
                    }

                    $assistants[] = [
                        'id' => $legacyAssistant->id,
                        'name' => $assistantName,
                        'position' => $legacyAssistant->position ?? 'Student Leader',
                        'organization_name' => $orgName,
                        'organization_id' => $legacyAssistant->organization_id ?? (optional($legacyAssistant->otherOrganizations->first())->id ?? null),
                        'image' => $assistantImage
                    ];
                }
                
                // Group assistants by organization name
                $assistants = collect($assistants)->groupBy('organization_name')->map(function($group) {
                    return $group->values()->all();
                })->toArray();
            }
            
            // Get organizations assigned to this staff member
            $organizations = [];
            if ($staff->organizations && $staff->organizations->isNotEmpty()) {
                foreach ($staff->organizations as $org) {
                    $organizations[] = [
                        'id' => $org->id,
                        'name' => $org->name
                    ];
                }
            } elseif ($staff->organization_id) {
                // Fallback to single organization_id if organizations relationship is empty
                $org = \App\Models\Organization::find($staff->organization_id);
                if ($org) {
                    $organizations[] = [
                        'id' => $org->id,
                        'name' => $org->name
                    ];
                }
            }

            $data['designationStaff'][] = [
                'id' => $staff->id,
                'name' => $staffName,
                'designation' => $staff->designation ?? 'Staff',
                'image' => $imageUrl,
                'organizations' => $organizations,
                'assistants' => $assistants
            ];
        }

        return $data;
    }

    /**
     * Build organizational structure: Staff → Assistants (for specific organization)
     */
    public function buildOrgStaffAssistantsStructure($organizationId)
    {
        $nodes = [];
        $edges = [];

        $organization = \App\Models\Organization::findOrFail($organizationId);

        // Get staff assigned to this organization
        $staffRecords = \App\Models\Staff::where('organization_id', $organizationId)
            ->orWhereHas('organizations', function($q) use ($organizationId) {
                $q->where('organizations.id', $organizationId);
            })
            ->with(['organizations', 'department'])
            ->get();

        // Get assistant assignments for this organization
        $assistants = \App\Models\AssistantAssignment::where('organization_id', $organizationId)
            ->where('active', true)
            ->with(['user', 'supervisor', 'organization'])
            ->get();

        // Add organization as root node
        $nodes[] = [
            'id' => 'org-' . $organization->id,
            'label' => $organization->name . '\n(Organization)',
            'level' => 0,
            'group' => 'organization',
            'title' => $organization->name . ' - Organization'
        ];

        // Add staff nodes
        foreach ($staffRecords as $staff) {
            $staffName = trim(($staff->first_name ?? '') . ' ' . ($staff->last_name ?? ''));
            if (empty($staffName)) {
                continue;
            }
            $designation = $staff->designation ?? 'Staff';
            
            $nodes[] = [
                'id' => 'staff-' . $staff->id,
                'label' => $staffName . '\n' . $designation,
                'level' => 1,
                'group' => 'staff',
                'title' => $staffName . ' - ' . $designation
            ];

            // Connect staff to organization
            $edges[] = [
                'from' => 'org-' . $organization->id,
                'to' => 'staff-' . $staff->id
            ];
        }

        // Add assistant nodes with hierarchy
        foreach ($assistants as $assistant) {
            if (!$assistant->user) {
                continue;
            }
            
            $assistantName = trim(($assistant->user->first_name ?? '') . ' ' . ($assistant->user->last_name ?? ''));
            if (empty($assistantName)) {
                continue;
            }
            
            $position = $assistant->position ?? 'Member';
            
            $nodes[] = [
                'id' => 'asst-' . $assistant->id,
                'label' => $assistantName . '\n' . $position,
                'level' => 2,
                'group' => 'assistant',
                'title' => $assistantName . ' - ' . $position
            ];

            // Connect to supervisor
            if ($assistant->supervisor_id) {
                // Check if supervisor is a Staff (by email matching)
                $supervisorUser = \App\Models\User::find($assistant->supervisor_id);
                $supervisorStaff = null;
                
                if ($supervisorUser) {
                    // Find staff by email match
                    $supervisorStaff = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($supervisorUser->email))])
                        ->where(function($q) use ($organizationId) {
                            $q->where('organization_id', $organizationId)
                              ->orWhereHas('organizations', function($q2) use ($organizationId) {
                                  $q2->where('organizations.id', $organizationId);
                              });
                        })
                        ->first();
                }

                if ($supervisorStaff) {
                    $edges[] = [
                        'from' => 'staff-' . $supervisorStaff->id,
                        'to' => 'asst-' . $assistant->id
                    ];
                } else {
                    // Check if supervisor is another assistant
                    $supervisorAsst = \App\Models\AssistantAssignment::where('user_id', $assistant->supervisor_id)
                        ->where('organization_id', $organizationId)
                        ->where('active', true)
                        ->first();
                    
                    if ($supervisorAsst) {
                        $edges[] = [
                            'from' => 'asst-' . $supervisorAsst->id,
                            'to' => 'asst-' . $assistant->id
                        ];
                    } else {
                        // Connect to organization if no supervisor found
                        $edges[] = [
                            'from' => 'org-' . $organization->id,
                            'to' => 'asst-' . $assistant->id
                        ];
                    }
                }
            } else {
                // Connect to organization if no supervisor
                $edges[] = [
                    'from' => 'org-' . $organization->id,
                    'to' => 'asst-' . $assistant->id
                ];
            }
        }

        return [
            'nodes' => $nodes,
            'edges' => $edges
        ];
    }

    /**
     * Safely validate and sanitize return_to URL parameter to prevent open redirects.
     * Rejects protocol-relative URLs (//evil.com) and external URLs.
     * Only allows relative paths starting with / (but not //).
     *
     * @param string|null $returnUrl
     * @return string|null Safe relative path or null if invalid
     */
    private function validateReturnUrl($returnUrl)
    {
        if (empty($returnUrl)) {
            return null;
        }

        // Decode URL encoding multiple times until no more encoding is found
        $decoded = $returnUrl;
        $previous = '';
        while ($decoded !== $previous && strpos($decoded, '%') !== false) {
            $previous = $decoded;
            $decoded = urldecode($decoded);
        }
        $returnUrl = $decoded; // $returnUrl is now fully decoded

        // Reject protocol-relative URLs (e.g., //evil.com/phishing)
        if (strpos($returnUrl, '//') === 0) {
            return null;
        }

        // Reject external URLs (http://, https://)
        if (preg_match('/^https?:\/\//', $returnUrl)) {
            // Extract only the path from external URLs for safety
            $parsed = parse_url($returnUrl);
            if (isset($parsed['path'])) {
                // $parsed['path'] is already decoded by parse_url, no need to decode again
                $path = $parsed['path'];
                // Ensure path starts with / and not //
                if (strpos($path, '//') === 0) {
                    return null;
                }
                $backUrl = (strpos($path, '/') === 0) ? $path : '/' . ltrim($path, '/');
                // Preserve query parameters if they exist
                if (isset($parsed['query'])) {
                    $backUrl .= '?' . $parsed['query'];
                }
                return $backUrl;
            }
            return null;
        }

        // For relative paths, ensure they start with / but not //
        // $returnUrl is already fully decoded above, no need to decode again
        $decodedPath = $returnUrl;
        if (strpos($decodedPath, '//') === 0) {
            return null; // Reject protocol-relative URLs
        }
        
        // Only allow paths starting with / (relative to current domain)
        if (strpos($decodedPath, '/') === 0) {
            return $decodedPath;
        }
        
        // If it doesn't start with /, prepend it (but still validate it's not //)
        $backUrl = '/' . ltrim($decodedPath, '/');
        if (strpos($backUrl, '//') === 0) {
            return null;
        }
        
        return $backUrl;
    }

    /**
     * Start participation monitoring for an event
     */
    public function startParticipationMonitoring($id)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }

        $event = \App\Models\Event::findOrFail($id);
        
        // Check if event has required student participation enabled
        if (!$event->required_student_participation) {
            return back()->with('error', 'This event does not have required student participation enabled.');
        }

        // Check if user has permission (admin or event creator)
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        
        if (!$isAdmin && $event->created_by !== $user->id && !($isStaff && $event->created_by === $user->id)) {
            abort(403, 'Unauthorized access.');
        }

        // Start monitoring
        $event->update([
            'monitoring_started' => true,
            'monitoring_started_at' => now(),
        ]);

        return back()->with('success', 'Participation monitoring has been started for this event.');
    }

    /**
     * Update monitoring threshold settings for an event
     */
    public function updateMonitoringThresholds($id, Request $request)
    {
        $access = $this->checkAdminOrOSAStaffAccess();
        if (!$access['hasAccess']) {
            return $this->redirectToDesignatedDashboard($access['user']);
        }

        $event = \App\Models\Event::findOrFail($id);
        
        // Check if event has required student participation enabled
        if (!$event->required_student_participation) {
            return back()->with('error', 'This event does not have required student participation enabled.');
        }

        // Check if user has permission (admin or event creator)
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        
        if (!$isAdmin && $event->created_by !== $user->id && !($isStaff && $event->created_by === $user->id)) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'attended_threshold_minutes' => 'required|integer|min:0',
            'late_threshold_minutes' => 'required|integer|min:0',
            'absent_threshold_minutes' => 'required|integer|min:0',
        ]);

        $event->update([
            'attended_threshold_minutes' => $request->attended_threshold_minutes,
            'late_threshold_minutes' => $request->late_threshold_minutes,
            'absent_threshold_minutes' => $request->absent_threshold_minutes,
        ]);

        return back()->with('success', 'Monitoring thresholds updated successfully.');
    }

    /**
     * Calculate attendance status based on scan time and event thresholds
     * Rules:
     * - Within attended_threshold_minutes (default 60) → "Attended"
     * - After attended_threshold_minutes but before absent_threshold_minutes (default 120) → "Late"
     * - After absent_threshold_minutes → still "Late" (unscanned students marked as "Absent" via automation)
     */
    public static function calculateAttendanceStatus($event, $scannedAt)
    {
        if (!$event->monitoring_started || !$event->monitoring_started_at) {
            return null; // Monitoring not started
        }

        // Use monitoring_started_at or event start_time as reference
        $referenceTime = $event->monitoring_started_at ?? $event->start_time;
        
        if (!$referenceTime) {
            return null;
        }

        $minutesSinceStart = $scannedAt->diffInMinutes($referenceTime, false);

        // If scanned before monitoring started, mark as Attended
        if ($minutesSinceStart < 0) {
            return 'Attended';
        }

        $attendedThreshold = $event->attended_threshold_minutes ?? 60;
        $absentThreshold = $event->absent_threshold_minutes ?? 120;

        // Apply thresholds
        if ($minutesSinceStart <= $attendedThreshold) {
            return 'Attended';
        } else {
            // After attended threshold, mark as Late (even if past absent threshold)
            // Absent status is only assigned to unscanned students via automation
            return 'Late';
        }
    }
}