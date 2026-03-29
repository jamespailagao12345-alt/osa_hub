<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Models\User;
use App\Models\Staff;

class StaffDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Use Staff records so we have designation and organization set from admin/add-staff
        // Load both single organization and multiple organizations relationships
        $staff = Staff::with(['department', 'organization', 'organizations'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $user = auth()->user();
        $currentUserDesignation = null;
        $currentUserStaffRecord = null;
        
        if ($user) {
            // Try to find the staff record for the current user by email (case-insensitive)
            $currentUserStaffRecord = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
                ->with(['department', 'organization', 'organizations'])
                ->first();
            
            $currentUserDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($currentUserStaffRecord ? $currentUserStaffRecord->designation : null);
        }
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;

        return view('admin.staff.dashboard', compact('staff', 'currentUserDesignation', 'currentUserStaffRecord', 'isAdmin', 'isStaff'));
    }

    public function showByDesignation(Request $request, string $designation)
    {
        // Normalize "Guidance Counsellor" (British spelling) to "Guidance Counselor" (American spelling) for consistency
        // This handles backward compatibility with existing data that may use British spelling
        $normalizedDesignation = trim($designation);
        if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
            $normalizedDesignation = 'Guidance Counselor';
        }
        
        $designationRecord = Designation::where('name', $normalizedDesignation)->first();
        if (!$designationRecord) {
            abort(404, 'Designation not found: ' . $designation);
        }
        // Access control: staff (role=2) can only view their own designation
        $user = auth()->user();
        if ($user && (int) $user->role === 2) {
            // Try to find staff record by email (case-insensitive)
            $staffRecord = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            // Normalize user designation for comparison (standardize on American spelling)
            // This handles backward compatibility with existing data that may use British spelling
            $normalizedUserDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedUserDesignation, 'Guidance Counsellor') === 0) {
                $normalizedUserDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedUserDesignation, $normalizedDesignation) !== 0) {
                $prev = url()->previous();
                if ($prev && $prev !== url()->current()) {
                    return redirect()->to($prev)->with('error', 'not allowed');
                }
                // Fallback to main staff dashboard
                return redirect()->route('admin.staff.dashboard')->with('error', 'not allowed');
            }
        }
        // If no worksheet in session, try to load from user's last_imported_worksheet
    if ($user && strcasecmp(str_replace(' ', '', $designation), 'AdmissionServicesOfficer') === 0 && !session('worksheetHtml')) {
            $lastFile = $user->last_imported_worksheet;
            if ($lastFile && file_exists(public_path($lastFile))) {
                $rows = [];
                $structured = [];
                $ext = pathinfo($lastFile, PATHINFO_EXTENSION);
                $fullPath = public_path($lastFile);
                try {
                    if ($ext === 'csv') {
                        $rows = array_map('str_getcsv', file($fullPath));
                    } else {
                        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
                        $sheet = $spreadsheet->getActiveSheet();
                        foreach ($sheet->toArray() as $row) {
                            $rows[] = $row;
                        }
                    }
                    $header = array_map('trim', $rows[0] ?? []);
                    for ($i = 1; $i < count($rows); $i++) {
                        $entry = [];
                        foreach ($header as $idx => $col) {
                            $entry[$col] = $rows[$i][$idx] ?? '';
                        }
                        $structured[] = $entry;
                    }
                    $html = '<table class="table table-bordered" contenteditable="true">';
                    foreach ($rows as $row) {
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $html .= '<td contenteditable="true">' . htmlspecialchars((string)$cell) . '</td>';
                        }
                        $html .= '</tr>';
                    }
                    $html .= '</table>';
                    session(['importedWorksheetData' => $structured]);
                    session(['worksheetHtml' => $html, 'worksheetFilePath' => $lastFile]);
                } catch (\Exception $e) {
                    // Ignore parse errors, just don't show worksheet
                }
            }
        }
        // Use Staff model to filter by designation and include relations
        // Use normalized designation to ensure we find the correct records
        $staff = \App\Models\Staff::where('designation', $normalizedDesignation)
            ->with(['department','organization'])
            ->paginate(15);

        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        
        // For Admission Services Officer and Prefect of Discipline, also pass all students with filters
        $students = collect([]);
        $departments = collect([]);
        $isAdmissionServicesOfficer = strcasecmp(str_replace(' ', '', $designation), 'AdmissionServicesOfficer') === 0;
        $isPrefectOfDiscipline = strcasecmp(str_replace(' ', '', $designation), 'PrefectofDiscipline') === 0 || strcasecmp($designation, 'Prefect of Discipline') === 0;
        if ($isAdmissionServicesOfficer || $isPrefectOfDiscipline) {
            // For Prefect of Discipline, always show students (even without filters)
            // For Admission Services Officer, require form submission (search button clicked)
            $formSubmitted = $request->has('search') || $request->has('department_id') || $request->has('year_level');
            // For Prefect of Discipline, always fetch students; for Admission Services Officer, require form submission
            if ($isPrefectOfDiscipline || $formSubmitted) {
                $search = $request->input('search', '');
                $hasSearch = $request->filled('search') && !empty(trim($search));
                $hasDepartment = $request->filled('department_id');
                $hasYearLevel = $request->filled('year_level');
                
                // Fetch ALL students from Student table (with user relationship)
                $studentsQuery = \App\Models\Student::with(['user', 'department','course','organization','scholarship']);
                
                // Only apply search filter if search term is provided and not empty
                if ($hasSearch) {
                    // Search only in related users table (student_information table doesn't have name/email columns)
                    $studentsQuery->whereHas('user', function($userQuery) use ($search) {
                        $userQuery->where('first_name', 'like', '%' . $search . '%')
                                  ->orWhere('last_name', 'like', '%' . $search . '%')
                                  ->orWhere('middle_name', 'like', '%' . $search . '%')
                                  ->orWhere('email', 'like', '%' . $search . '%')
                                  ->orWhere('user_id', 'like', '%' . $search . '%')
                                  ->orWhere('contact_number', 'like', '%' . $search . '%');
                    });
                }
                
                // Department filter - check both tables (only if department_id is provided and not empty)
                if ($hasDepartment) {
                    $studentsQuery->where(function($q) use ($request) {
                        $q->where('department_id', $request->department_id)
                          ->orWhereHas('user', function($userQuery) use ($request) {
                              $userQuery->where('department_id', $request->department_id);
                          });
                    });
                }
                
                // Year level filter - check both tables (only if year_level is provided and not empty)
                if ($hasYearLevel) {
                    $studentsQuery->where(function($q) use ($request) {
                        $q->whereHas('user.studentInformation', function($siQuery) use ($request) {
                            $siQuery->where('year_level', $request->year_level);
                        });
                    });
                }
                
                $studentsFromStudentTable = $studentsQuery->get();
                
                // Fetch ALL students from User table (role = 1) that don't have Student records yet
                // This ensures we get students that exist in users table but not yet in students table
                $usersQuery = \App\Models\User::with(['department','course','organization','scholarship'])
                    ->where('role', 1) // Only students
                    ->whereDoesntHave('student'); // Exclude users that already have Student records (to avoid duplicates)
                
                // Only apply search filter if search term is provided and not empty
                if ($hasSearch) {
                    $usersQuery->where(function($q) use ($search) {
                        $q->where('first_name', 'like', '%' . $search . '%')
                          ->orWhere('last_name', 'like', '%' . $search . '%')
                          ->orWhere('middle_name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%')
                          ->orWhere('user_id', 'like', '%' . $search . '%')
                          ->orWhere('contact_number', 'like', '%' . $search . '%');
                    });
                }
                
                // Department filter for users (only if department_id is provided and not empty)
                if ($hasDepartment) {
                    $usersQuery->where('department_id', $request->department_id);
                }
                
                // Year level filter for users (only if year_level is provided and not empty)
                if ($hasYearLevel) {
                    $usersQuery->whereHas('studentInformation', function($q) use ($request) {
                        $q->where('year_level', $request->year_level);
                    });
                }
                
                $usersFromUserTable = $usersQuery->get();
                
                // Automatically sync User records to Student table
                foreach ($usersFromUserTable as $user) {
                    $this->syncUserToStudent($user);
                }
                
                // Re-fetch to include newly synced students (now they have Student records)
                // We need to re-query Students instead since users now have student records
                if ($usersFromUserTable->isNotEmpty()) {
                    $syncedStudents = \App\Models\Student::with(['user', 'department','course','organization','scholarship'])
                        ->whereIn('user_id', $usersFromUserTable->pluck('id'))
                        ->get();
                    
                    // Add synced students to the main collection (avoid duplicates)
                    $existingUserIds = $studentsFromStudentTable->pluck('user_id')->toArray();
                    $newStudents = $syncedStudents->reject(function($student) use ($existingUserIds) {
                        return in_array($student->user_id, $existingUserIds);
                    });
                    $studentsFromStudentTable = $studentsFromStudentTable->merge($newStudents);
                }
                
                // Combine and format results, then sort alphabetically
                $studentsCollection = $studentsFromStudentTable
                    ->map(function($student) {
                        // Mark as Student model
                        $student->isStudentModel = true;
                        $student->isUserModel = false;
                        // Merge user data into student for easier access
                        if ($student->user) {
                            $student->user_id_display = $student->user->user_id;
                            // Sync any missing data from user to student for display
                            if (!$student->email && $student->user->email) {
                                $student->email = $student->user->email;
                            }
                            if (!$student->contact_number && $student->user->contact_number) {
                                $student->contact_number = $student->user->contact_number;
                            }
                            if (!$student->user_id && $student->user->user_id) {
                                $student->user_id = $student->user->user_id;
                            }
                        }
                        return $student;
                    })
                    ->sortBy(function($item) {
                        // Sort alphabetically by last name, then first name (case-insensitive)
                        $lastName = strtolower(($item->last_name ?? optional($item->user)->last_name) ?? '');
                        $firstName = strtolower(($item->first_name ?? optional($item->user)->first_name) ?? '');
                        return $lastName . ' ' . $firstName;
                    })
                    ->values();
                
                // Paginate students for better performance
                $perPage = 50;
                $currentPage = $request->get('page', 1);
                $totalStudents = $studentsCollection->count();
                $offset = ($currentPage - 1) * $perPage;
                $paginatedStudents = $studentsCollection->slice($offset, $perPage)->values();
                
                // Create paginator manually for Prefect of Discipline and Admission Services Officer
                $students = new \Illuminate\Pagination\LengthAwarePaginator(
                    $paginatedStudents,
                    $totalStudents,
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
            
            // Get departments for filter dropdown
            $departments = \App\Models\Department::orderBy('name')->get();
        }
        
        // Get all approved events with Required Student Participation ON for QR scanner dropdown
        $events = \App\Models\Event::where('status', 'approved')
            ->where('required_student_participation', true)
            ->orderBy('event_date', 'desc')
            ->get();
        
        // For Guidance Counselor, fetch and group appointments by reason_for_counseling
        $appointmentsByReason = collect([]);
        if (strcasecmp($normalizedDesignation, 'Guidance Counselor') === 0) {
            // Get the current user
            $currentUser = auth()->user();
            $currentUserId = $currentUser ? $currentUser->id : null;
            
            // Build query for appointments
            if ($isStaff && !$isAdmin && $currentUserId) {
                // For staff users: Get appointments assigned to them that have reason_for_counseling
                $appointments = \App\Models\Appointment::where('assigned_staff_id', $currentUserId)
                    ->whereNotNull('reason_for_counseling')
                    ->with(['assignedStaff', 'user'])
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('appointment_time', 'desc')
                    ->get();
            } else {
                // For admins: Get all appointments for Guidance Counselor
                // Find Guidance Counselor staff user IDs from User table
                $guidanceCounselorUserIds = \App\Models\User::where('role', 2)
                    ->where(function($query) use ($normalizedDesignation) {
                        $query->where('designation', $normalizedDesignation)
                              ->orWhere('designation', 'Guidance Counsellor');
                    })
                    ->pluck('id')
                    ->toArray();
                
                // Also check Staff table for Guidance Counselor emails
                $guidanceCounselorStaffEmails = \App\Models\Staff::where(function($query) use ($normalizedDesignation) {
                        $query->where('designation', $normalizedDesignation)
                              ->orWhere('designation', 'Guidance Counsellor');
                    })
                    ->pluck('email')
                    ->toArray();
                
                // Get user IDs from Staff emails
                if (!empty($guidanceCounselorStaffEmails)) {
                    $staffUserIds = \App\Models\User::whereIn('email', $guidanceCounselorStaffEmails)
                        ->where('role', 2)
                        ->pluck('id')
                        ->toArray();
                    $guidanceCounselorUserIds = array_unique(array_merge($guidanceCounselorUserIds, $staffUserIds));
                }
                
                // Get appointments assigned to Guidance Counselor staff that have reason_for_counseling
                // OR appointments with Guidance Counselor concern that have reason_for_counseling
                $appointments = \App\Models\Appointment::where(function($query) use ($guidanceCounselorUserIds) {
                        // Appointments assigned to Guidance Counselor staff
                        if (!empty($guidanceCounselorUserIds)) {
                            $query->whereIn('assigned_staff_id', $guidanceCounselorUserIds);
                        }
                        // OR appointments with Guidance Counselor concern
                        $query->orWhere(function($concernQuery) {
                            $concernQuery->where('concern', 'like', '%Guidance Counselor%')
                                         ->orWhere('concern', 'like', '%Guidance Counsellor%');
                        });
                    })
                    ->whereNotNull('reason_for_counseling')
                    ->with(['assignedStaff', 'user'])
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('appointment_time', 'desc')
                    ->get();
            }
            
            // Group appointments by reason_for_counseling
            // Map reason_for_counseling values to service names (case-insensitive matching)
            $appointmentsByReason = $appointments->groupBy(function($appointment) {
                // Normalize the reason_for_counseling to match service names
                $reason = trim($appointment->reason_for_counseling ?? '');
                
                // Map to standard service names
                $serviceMap = [
                    'Initial Interview' => 'Initial Interview',
                    'Information Services' => 'Information Services',
                    'Counseling Services' => 'Counseling Services',
                    'External Referral' => 'External Referral',
                    'Internal Referral' => 'Internal Referral',
                    'Exit Interview' => 'Exit Interview',
                ];
                
                // Case-insensitive matching
                foreach ($serviceMap as $key => $value) {
                    if (strcasecmp($reason, $key) === 0) {
                        return $value;
                    }
                }
                
                // If no match, return the original reason (might be a typo or new value)
                return $reason;
            });
        }
        
        // For OSA Staff, use the same UI as admin dashboard
        $isOSAStaff = strcasecmp($normalizedDesignation, 'OSA Staff') === 0;
        
        if ($isOSAStaff) {
            // Pass the same data as admin dashboard
            $pendingEvents = \App\Models\Event::where('status', 'pending')->with('creator')->get();
            $approvedEvents = \App\Models\Event::where('status', 'approved')->with('creator')->get();
            $staff = \App\Models\User::where('role', 2)->get();
            $appointments = \App\Models\Appointment::where('status', 'pending')->with('user', 'assignedStaff')->get();
            
            return view('admin.dashboard', compact('pendingEvents', 'approvedEvents', 'staff', 'appointments'));
        }
        
        // Dashboard Overview data for Nurse
        $dashboardOverview = null;
        if (strcasecmp($normalizedDesignation, 'Nurse') === 0) {
            $currentUserId = $user->id ?? null;
            
            // Appointments On Queue (pending appointments assigned to the nurse)
            $appointmentsOnQueue = \App\Models\Appointment::where('assigned_staff_id', $currentUserId)
                ->where('status', 'pending')
                ->count();
            
            // Recent Cases (recent appointments handled by the nurse, limit to last 10)
            $recentCases = \App\Models\Appointment::where('assigned_staff_id', $currentUserId)
                ->whereIn('status', ['approved', 'completed', 'rescheduled'])
                ->with('user')
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->take(10)
                ->get();
            
            // Accounts: Number of unique students and faculty served by the nurse
            // Only count appointments where session = 'Finish' (completed sessions)
            // Get unique user IDs from finished appointments (only those with user_id to match with User table)
            $servedUserIds = \App\Models\Appointment::where('assigned_staff_id', $currentUserId)
                ->where('session', 'Finish')
                ->whereNotNull('user_id')
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            
            // Count students (role = 1)
            $studentCount = \App\Models\User::whereIn('id', $servedUserIds)
                ->where('role', 1)
                ->count();
            
            // Count faculty/staff (role = 2)
            $facultyCount = \App\Models\User::whereIn('id', $servedUserIds)
                ->where('role', 2)
                ->count();
            
            $dashboardOverview = [
                'appointmentsOnQueue' => $appointmentsOnQueue,
                'recentCases' => $recentCases,
                'studentCount' => $studentCount,
                'facultyCount' => $facultyCount,
            ];
        }
        
        return view('admin.staff.designation-dashboard', [
            'designation' => $designationRecord,
            'staff' => $staff,
            'isAdmin' => $isAdmin,
            'isStaff' => $isStaff,
            'students' => $students,
            'departments' => $departments,
            'events' => $events,
            'filters' => $request->only(['search', 'department_id', 'year_level']),
            'appointmentsByReason' => $appointmentsByReason,
            'dashboardOverview' => $dashboardOverview,
        ]);
    }
    
    /**
     * Show appointments for a specific Guidance Counselor service
     */
    public function showGuidanceCounselorService(Request $request, string $designation, string $service)
    {
        // Decode URL-encoded designation (Laravel should do this automatically, but be safe)
        $designation = urldecode($designation);
        $service = urldecode($service);
        
        // Normalize designation
        $normalizedDesignation = trim($designation);
        if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
            $normalizedDesignation = 'Guidance Counselor';
        }
        
        // Validate designation
        $designationRecord = Designation::where('name', $normalizedDesignation)->first();
        if (!$designationRecord || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
            abort(404, 'Service page not found for this designation.');
        }
        
        // Validate service name
        $validServices = [
            'Initial Interview',
            'Information Services',
            'Counseling Services',
            'External Referral',
            'Internal Referral',
            'Exit Interview',
        ];
        
        // Normalize service name (case-insensitive matching)
        $normalizedService = null;
        foreach ($validServices as $validService) {
            if (strcasecmp(trim($service), $validService) === 0) {
                $normalizedService = $validService;
                break;
            }
        }
        
        if (!$normalizedService) {
            abort(404, 'Invalid service name: ' . $service);
        }
        
        // Access control
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        $currentUserId = $user ? $user->id : null;
        
        if ($isStaff && !$isAdmin) {
            // Check if user is Guidance Counselor
            $staffRecord = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            $normalizedUserDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedUserDesignation, 'Guidance Counsellor') === 0) {
                $normalizedUserDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedUserDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can access this page.');
            }
        }
        
        // Fetch appointments for this service
        if ($isStaff && !$isAdmin && $currentUserId) {
            // For staff users: Get appointments assigned to them with this reason_for_counseling
            $appointments = \App\Models\Appointment::where('assigned_staff_id', $currentUserId)
                ->where('reason_for_counseling', $normalizedService)
                ->with(['assignedStaff', 'user'])
                ->orderByRaw("CASE WHEN session = 'Finish' THEN 1 ELSE 0 END ASC")
                ->orderByRaw('COALESCE(rescheduled_date, appointment_date) ASC')
                ->orderByRaw('COALESCE(rescheduled_time, appointment_time) ASC')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // For admins: Get all appointments for Guidance Counselor with this reason_for_counseling
            $guidanceCounselorUserIds = \App\Models\User::where('role', 2)
                ->where(function($query) use ($normalizedDesignation) {
                    $query->where('designation', $normalizedDesignation)
                          ->orWhere('designation', 'Guidance Counsellor');
                })
                ->pluck('id')
                ->toArray();
            
            $guidanceCounselorStaffEmails = \App\Models\Staff::where(function($query) use ($normalizedDesignation) {
                    $query->where('designation', $normalizedDesignation)
                          ->orWhere('designation', 'Guidance Counsellor');
                })
                ->pluck('email')
                ->toArray();
            
            if (!empty($guidanceCounselorStaffEmails)) {
                $staffUserIds = \App\Models\User::whereIn('email', $guidanceCounselorStaffEmails)
                    ->where('role', 2)
                    ->pluck('id')
                    ->toArray();
                $guidanceCounselorUserIds = array_unique(array_merge($guidanceCounselorUserIds, $staffUserIds));
            }
            
            // Get appointments assigned to Guidance Counselor staff OR with Guidance Counselor concern
            $appointments = \App\Models\Appointment::where(function($query) use ($guidanceCounselorUserIds) {
                    if (!empty($guidanceCounselorUserIds)) {
                        $query->whereIn('assigned_staff_id', $guidanceCounselorUserIds);
                    }
                    $query->orWhere(function($concernQuery) {
                        $concernQuery->where('concern', 'like', '%Guidance Counselor%')
                                     ->orWhere('concern', 'like', '%Guidance Counsellor%');
                    });
                })
                ->where('reason_for_counseling', $normalizedService)
                ->with(['assignedStaff', 'user'])
                ->orderByRaw("CASE WHEN session = 'Finish' THEN 1 ELSE 0 END ASC")
                ->orderByRaw('COALESCE(rescheduled_date, appointment_date) ASC')
                ->orderByRaw('COALESCE(rescheduled_time, appointment_time) ASC')
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        }
        
        // Service configuration
        $serviceConfigs = [
            'Initial Interview' => ['color' => 'primary', 'icon' => 'person-check'],
            'Information Services' => ['color' => 'info', 'icon' => 'info-circle'],
            'Counseling Services' => ['color' => 'success', 'icon' => 'chat-dots'],
            'External Referral' => ['color' => 'warning', 'icon' => 'arrow-up-right-circle'],
            'Internal Referral' => ['color' => 'secondary', 'icon' => 'arrow-right-circle'],
            'Exit Interview' => ['color' => 'danger', 'icon' => 'person-x'],
        ];
        
        $serviceConfig = $serviceConfigs[$normalizedService] ?? ['color' => 'secondary', 'icon' => 'circle'];
        $returnPath = '/admin/staff/dashboard/' . $designation;
        
        return view('admin.staff.dashboard.guidance-counselor.service', [
            'designation' => $designationRecord,
            'service' => $normalizedService,
            'serviceConfig' => $serviceConfig,
            'appointments' => $appointments,
            'isAdmin' => $isAdmin,
            'isStaff' => $isStaff,
            'returnPath' => $returnPath,
        ]);
    }
    
    /**
     * Show clients list for Guidance Counselor
     */
    public function showGuidanceCounselorClients(Request $request, string $designation)
    {
        // Normalize designation
        $normalizedDesignation = trim($designation);
        if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
            $normalizedDesignation = 'Guidance Counselor';
        }
        
        // Validate designation
        $designationRecord = Designation::where('name', $normalizedDesignation)->first();
        if (!$designationRecord || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
            abort(404, 'Clients list page not found for this designation.');
        }
        
        // Access control
        $user = auth()->user();
        $isAdmin = $user && (int) $user->role === 4;
        $isStaff = $user && (int) $user->role === 2;
        $currentUserId = $user ? $user->id : null;
        
        if ($isStaff && !$isAdmin) {
            // Check if user is Guidance Counselor
            $staffRecord = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])->first();
            $userDesignation = $user->designation
                ?? optional($user->staffProfile)->designation
                ?? ($staffRecord ? $staffRecord->designation : null);
            
            $normalizedUserDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedUserDesignation, 'Guidance Counsellor') === 0) {
                $normalizedUserDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedUserDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can access this page.');
            }
        }
        
        // Fetch clients (unique people who have appointments with Guidance Counselor)
        if ($isStaff && !$isAdmin && $currentUserId) {
            // For staff users: Get unique clients from appointments assigned to them
            $appointments = \App\Models\Appointment::where('assigned_staff_id', $currentUserId)
                ->whereNotNull('reason_for_counseling')
                ->with(['assignedStaff', 'user'])
                ->get();
        } else {
            // For admins: Get unique clients from all appointments for Guidance Counselor
            $guidanceCounselorUserIds = \App\Models\User::where('role', 2)
                ->where(function($query) use ($normalizedDesignation) {
                    $query->where('designation', $normalizedDesignation)
                          ->orWhere('designation', 'Guidance Counsellor');
                })
                ->pluck('id')
                ->toArray();
            
            $guidanceCounselorStaffEmails = \App\Models\Staff::where(function($query) use ($normalizedDesignation) {
                    $query->where('designation', $normalizedDesignation)
                          ->orWhere('designation', 'Guidance Counsellor');
                })
                ->pluck('email')
                ->toArray();
            
            if (!empty($guidanceCounselorStaffEmails)) {
                $staffUserIds = \App\Models\User::whereIn('email', $guidanceCounselorStaffEmails)
                    ->where('role', 2)
                    ->pluck('id')
                    ->toArray();
                $guidanceCounselorUserIds = array_unique(array_merge($guidanceCounselorUserIds, $staffUserIds));
            }
            
            // Get appointments assigned to Guidance Counselor staff OR with Guidance Counselor concern
            $appointments = \App\Models\Appointment::where(function($query) use ($guidanceCounselorUserIds) {
                    if (!empty($guidanceCounselorUserIds)) {
                        $query->whereIn('assigned_staff_id', $guidanceCounselorUserIds);
                    }
                    $query->orWhere(function($concernQuery) {
                        $concernQuery->where('concern', 'like', '%Guidance Counselor%')
                                     ->orWhere('concern', 'like', '%Guidance Counsellor%');
                    });
                })
                ->whereNotNull('reason_for_counseling')
                ->with(['assignedStaff', 'user'])
                ->get();
        }
        
        // Extract unique clients from appointments
        // Group by email (primary) or user_id (fallback), then get unique clients
        $clientsMap = [];
        
        foreach ($appointments as $appointment) {
            // Determine client identifier (email first, then user_id)
            $clientIdentifier = null;
            $clientEmail = null;
            $clientUserId = null;
            
            if (!empty($appointment->email)) {
                $clientEmail = strtolower(trim($appointment->email));
                $clientIdentifier = 'email:' . $clientEmail;
            } elseif ($appointment->user_id) {
                $clientUserId = $appointment->user_id;
                $clientIdentifier = 'user:' . $appointment->user_id;
            }
            
            if (!$clientIdentifier) {
                continue; // Skip appointments without email or user_id
            }
            
            // Initialize client data if not exists
            if (!isset($clientsMap[$clientIdentifier])) {
                if ($clientEmail) {
                    // Use email as identifier
                    $clientsMap[$clientIdentifier] = [
                        'email' => $appointment->email,
                        'full_name' => $appointment->full_name,
                        'contact_number' => $appointment->contact_number,
                        'user_id' => $appointment->user_id,
                        'user' => $appointment->user,
                        'appointment_count' => 0,
                        'last_appointment_date' => null,
                        'categories' => [],
                        'reasons' => [],
                    ];
                } else {
                    // Use user_id as identifier
                    $user = $appointment->user;
                    $clientsMap[$clientIdentifier] = [
                        'email' => $user->email ?? '-',
                        'full_name' => ($user->first_name ?? '') . ' ' . ($user->last_name ?? ''),
                        'contact_number' => $user->contact_number ?? $appointment->contact_number ?? '-',
                        'user_id' => $appointment->user_id,
                        'user' => $user,
                        'appointment_count' => 0,
                        'last_appointment_date' => null,
                        'categories' => [],
                        'reasons' => [],
                    ];
                }
            }
            
            // Update client data with appointment information
            $client = &$clientsMap[$clientIdentifier];
            $client['appointment_count']++;
            
            // Update last appointment date
            $appointmentDate = $appointment->action_taken === 'reschedule' && $appointment->rescheduled_date 
                ? \Carbon\Carbon::parse($appointment->rescheduled_date) 
                : $appointment->appointment_date;
            
            if (!$client['last_appointment_date'] || 
                ($appointmentDate && $appointmentDate > $client['last_appointment_date'])) {
                $client['last_appointment_date'] = $appointmentDate;
            }
            
            // Collect categories
            if ($appointment->category) {
                $client['categories'][] = $appointment->category;
            }
            
            // Collect reasons for counseling
            if ($appointment->reason_for_counseling) {
                $client['reasons'][] = $appointment->reason_for_counseling;
            }
        }
        
        // Convert to collection and process
        $clients = collect($clientsMap)->map(function($client) use ($appointments) {
            // Get unique categories and reasons
            $client['categories'] = collect($client['categories'])->unique()->values()->toArray();
            $client['reasons'] = collect($client['reasons'])->unique()->values()->toArray();
            
            // Get all appointments for this client
            $clientEmail = strtolower(trim($client['email'] ?? ''));
            $clientUserId = $client['user_id'] ?? null;
            
            $clientAppointments = $appointments->filter(function($apt) use ($clientEmail, $clientUserId) {
                if (!empty($clientEmail) && !empty($apt->email)) {
                    return strtolower(trim($apt->email)) === $clientEmail;
                }
                if ($clientUserId && $apt->user_id) {
                    return $apt->user_id === $clientUserId;
                }
                return false;
            })->sortByDesc(function($apt) {
                $date = $apt->action_taken === 'reschedule' && $apt->rescheduled_date 
                    ? \Carbon\Carbon::parse($apt->rescheduled_date) 
                    : $apt->appointment_date;
                return $date ? $date->timestamp : 0;
            })->values();
            
            // Add appointments to client data
            $client['appointments'] = $clientAppointments;
            
            return $client;
        })->sortByDesc('last_appointment_date')->values();
        
        // Paginate clients
        $perPage = 15;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedClients = $clients->slice($offset, $perPage)->values();
        
        // Create paginator manually
        $clientsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedClients,
            $clients->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        $returnPath = '/admin/staff/dashboard/' . $designation;
        
        return view('admin.staff.dashboard.guidance-counselor.clients', [
            'designation' => $designationRecord,
            'clients' => $clientsPaginator,
            'isAdmin' => $isAdmin,
            'isStaff' => $isStaff,
            'returnPath' => $returnPath,
        ]);
    }
    
    /**
     * Sync a User record (role = 1) to Student table
     * This ensures all students exist in both tables
     */
    private function syncUserToStudent(\App\Models\User $user)
    {
        // Check if Student record already exists
        $existingStudent = \App\Models\Student::where('user_id', $user->id)->first();
        
        // Use the email from the user record
        $email = $user->email ?? null;
        
        if ($existingStudent) {
            // Update existing Student record - only update columns that exist in student_information table
            // Note: first_name, last_name, email, etc. are in users table, not student_information
            $updateData = [
                'scholarship_id' => optional($user->studentInformation)->scholarship_id ?? $existingStudent->scholarship_id,
            ];
            
            // Only update fields that exist in student_information table
            if ($user->studentInformation) {
                if ($user->studentInformation->year_level !== null) {
                    $updateData['year_level'] = $user->studentInformation->year_level;
                }
                if ($user->studentInformation->student_type1 !== null) {
                    $updateData['student_type1'] = $user->studentInformation->student_type1;
                }
                if ($user->studentInformation->student_type2 !== null) {
                    $updateData['student_type2'] = $user->studentInformation->student_type2;
                }
                if ($user->studentInformation->student_type !== null) {
                    $updateData['student_type'] = $user->studentInformation->student_type;
                }
                if ($user->studentInformation->school_year !== null) {
                    $updateData['school_year'] = $user->studentInformation->school_year;
                }
                if ($user->studentInformation->semester !== null) {
                    $updateData['semester'] = $user->studentInformation->semester;
                }
                if ($user->studentInformation->academic_year !== null) {
                    $updateData['academic_year'] = $user->studentInformation->academic_year;
                }
                $updateData['is_active_scholar'] = $user->studentInformation->is_active_scholar ?? false;
                if ($user->studentInformation->scholarship_grant_name !== null) {
                    $updateData['scholarship_grant_name'] = $user->studentInformation->scholarship_grant_name;
                }
            }
            
            $existingStudent->update($updateData);
            return $existingStudent;
        }
        
        // Prepare student data array - only include columns that exist in student_information table
        // Note: first_name, last_name, email, etc. are in users table, not student_information
        $studentData = [
            'user_id' => $user->id,
            'student_id' => $user->user_id ?? null, // Copy user_id as student_id if exists
            'scholarship_id' => optional($user->studentInformation)->scholarship_id ?? null,
        ];
        
        // Only add fields that exist in student_information table
        if ($user->studentInformation) {
            $studentData['year_level'] = $user->studentInformation->year_level ?? null;
            $studentData['student_type1'] = $user->studentInformation->student_type1 ?? null;
            $studentData['student_type2'] = $user->studentInformation->student_type2 ?? null;
            $studentData['student_type'] = $user->studentInformation->student_type ?? null;
            $studentData['school_year'] = $user->studentInformation->school_year ?? null;
            $studentData['semester'] = $user->studentInformation->semester ?? null;
            $studentData['academic_year'] = $user->studentInformation->academic_year ?? null;
            $studentData['is_active_scholar'] = $user->studentInformation->is_active_scholar ?? false;
            $studentData['scholarship_grant_name'] = $user->studentInformation->scholarship_grant_name ?? null;
        }
        
        // Create new Student record from User data (only student_information table columns)
        $student = \App\Models\Student::create($studentData);
        
        return $student;
    }
    /**
     * Handle Excel/CSV import for Admission Services Officer
     */
    public function importWorksheet(Request $request)
    {
        $user = auth()->user();
        // Try all possible sources for designation
        $designation = $user?->designation
            ?? optional($user?->staffProfile)->designation
            ?? \App\Models\Staff::where('email', $user?->email)->value('designation')
            ?? '';
        // Only allow Admission Services Officer or Admin
    if (strcasecmp(str_replace(' ', '', $designation), 'AdmissionServicesOfficer') !== 0 && (int)($user?->role ?? 0) !== 4) {
            return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')
                ->withErrors(['worksheetFile' => 'You are not allowed to import files.']);
        }
        $request->validate([
            'worksheetFile' => 'required|file|mimes:xlsx,csv',
        ]);
        $file = $request->file('worksheetFile');
        // Save the uploaded file to public/staff/sidebar/report/
        $filename = 'worksheet_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('staff/sidebar/report'), $filename);
        $rows = [];
        $structured = [];
        // Use PhpSpreadsheet for parsing
        try {
            $ext = $file->getClientOriginalExtension();
            $fullPath = public_path('staff/sidebar/report/' . $filename);
            if ($ext === 'csv') {
                $rows = array_map('str_getcsv', file($fullPath));
            } else {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
                $sheet = $spreadsheet->getActiveSheet();
                foreach ($sheet->toArray() as $row) {
                    $rows[] = $row;
                }
            }
            // Assume first row is header
            $header = array_map('trim', $rows[0] ?? []);
            for ($i = 1; $i < count($rows); $i++) {
                $entry = [];
                foreach ($header as $idx => $col) {
                    $entry[$col] = $rows[$i][$idx] ?? '';
                }
                $structured[] = $entry;
            }
        } catch (\Exception $e) {
            return back()->withErrors(['worksheetFile' => 'Failed to parse file: ' . $e->getMessage()]);
        }
        // Render as editable HTML table
        $html = '<table class="table table-bordered" contenteditable="true">';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td contenteditable="true">' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        // Debug: log import process
        \Log::info('Excel import processed for user: ' . ($user->email ?? 'unknown'));
        \Log::info('Imported rows count: ' . count($rows));
        // Store structured data in session for search/edit
        session(['importedWorksheetData' => $structured]);
        // Save file path to user for persistence
        $user->last_imported_worksheet = 'staff/sidebar/report/' . $filename;
        $user->save();
    // Always redirect to the Admission Services Officer student-management page so the table displays
    return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')
            ->with(['worksheetHtml' => $html, 'worksheetFilePath' => 'staff/sidebar/report/' . $filename]);
    }
}
