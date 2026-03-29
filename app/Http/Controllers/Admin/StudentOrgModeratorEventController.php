<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Organization;

class StudentOrgModeratorEventController extends Controller
{
    
    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // If admin is viewing, check for staff email or ID in query parameter
        $targetStaff = null;
        $targetUser = null;
        
        if ($isAdmin && $request->has('staff_email')) {
            // Admin viewing a specific staff member's dashboard
            $targetStaff = \App\Models\Staff::with(['organizations', 'organization'])->where('email', $request->staff_email)->first();
            if ($targetStaff) {
                $targetUser = \App\Models\User::where('email', $targetStaff->email)->first();
            }
        } elseif ($isAdmin && $request->has('staff_id')) {
            // Admin viewing a specific staff member's dashboard by ID
            $targetStaff = \App\Models\Staff::with(['organizations', 'organization'])->find($request->staff_id);
            if ($targetStaff) {
                $targetUser = \App\Models\User::where('email', $targetStaff->email)->first();
            }
        }
        
        // Use target staff/user if admin is viewing, otherwise use current user
        $staff = $targetStaff ?? \App\Models\Staff::with(['organizations', 'organization'])->where('email', $currentUser->email)->first();
        $user = $targetUser ?? $currentUser;
        
        // Access control: Allow all staff (role 2) and admins (role 4) to create events
        if (!$isAdmin && (int) $currentUser->role !== 2) {
            abort(403, 'Unauthorized: Only staff members can create events.');
        }
        
        $userOrganizations = collect();
        
        // If no staff found by email, try to find by user ID (for staff who are users)
        if (!$staff && $user && (int) $user->role === 2) {
            // Try to find staff by user_id (if user has a user_id field pointing to staff)
            if (isset($user->user_id) && $user->user_id) {
                $staff = \App\Models\Staff::where('user_id', $user->user_id)
                    ->with(['organizations', 'organization'])
                    ->first();
            }
            
            // Also try to find staff by matching the user's ID with staff's user_id
            if (!$staff) {
                $staff = \App\Models\Staff::where('user_id', $user->id)
                    ->with(['organizations', 'organization'])
                    ->first();
            }
            
            // Also try to find staff by ID if user has a user_id or similar
            if (!$staff && isset($user->user_id)) {
                $staff = \App\Models\Staff::where('staff_id', $user->user_id)->first();
                if ($staff) {
                    $staff->load(['organizations', 'organization']);
                }
            }
        }
        
        // Get organizations from Staff record if it exists
        if ($staff) {
            // Get single organization from organization_id (if set)
            if ($staff->organization_id) {
                $org = $staff->organization ?? Organization::find($staff->organization_id);
                if ($org) {
                    $userOrganizations->push($org);
                }
            }
            
            // Get additional organizations from many-to-many relationship (organization_staff table)
            $additionalOrgs = $staff->organizations()->get();
            foreach ($additionalOrgs as $org) {
                // Avoid duplicates
                if (!$userOrganizations->contains('id', $org->id)) {
                    $userOrganizations->push($org);
                }
            }
        }
        
        // Also check if user has organizations directly (for staff who are users)
        if ($user && $user->organization_id) {
            $org = Organization::find($user->organization_id);
            if ($org && !$userOrganizations->contains('id', $org->id)) {
                $userOrganizations->push($org);
            }
        }
        
        // Get additional organizations from organization_user table
        if ($user && method_exists($user, 'otherOrganizations')) {
            $userOrgs = $user->otherOrganizations()->get();
            foreach ($userOrgs as $org) {
                if (!$userOrganizations->contains('id', $org->id)) {
                    $userOrganizations->push($org);
                }
            }
        }
        
        // If staff record exists but we didn't find organizations, also check organization_staff table directly
        // This handles cases where staff might be assigned organizations but the relationship isn't loading properly
        if ($staff && $userOrganizations->isEmpty()) {
            $directOrgIds = \Illuminate\Support\Facades\DB::table('organization_staff')
                ->where('staff_id', $staff->id)
                ->pluck('organization_id');
            
            if ($directOrgIds->isNotEmpty()) {
                $directOrgs = Organization::whereIn('id', $directOrgIds)->get();
                foreach ($directOrgs as $org) {
                    if (!$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
            }
        }
        
        // If still no organizations found and user is staff (role 2), check organization_staff by user's email
        // Some staff might be linked through email matching
        if ($userOrganizations->isEmpty() && $user && (int) $user->role === 2) {
            // Try to find staff by email (case-insensitive)
            $staffByEmail = \App\Models\Staff::whereRaw('LOWER(email) = ?', [strtolower($user->email)])
                ->with(['organizations', 'organization'])
                ->first();
            
            if ($staffByEmail) {
                if ($staffByEmail->organization_id) {
                    $org = Organization::find($staffByEmail->organization_id);
                    if ($org && !$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
                
                $additionalOrgs = $staffByEmail->organizations()->get();
                foreach ($additionalOrgs as $org) {
                    if (!$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
            }
        }
        
        // Final check: If user is staff but we still have no organizations, check organization_staff table
        // by finding all staff records that might be linked to this user
        if ($userOrganizations->isEmpty() && $user && (int) $user->role === 2) {
            // Try to find any staff record that might be related to this user
            $allStaffRecords = \App\Models\Staff::where(function($query) use ($user) {
                    $query->where('email', $user->email)
                          ->orWhere('user_id', $user->id)
                          ->orWhereRaw('LOWER(email) = ?', [strtolower($user->email)]);
                })
                ->with(['organizations', 'organization'])
                ->get();
            
            foreach ($allStaffRecords as $staffRecord) {
                if ($staffRecord->organization_id) {
                    $org = Organization::find($staffRecord->organization_id);
                    if ($org && !$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
                
                $additionalOrgs = $staffRecord->organizations()->get();
                foreach ($additionalOrgs as $org) {
                    if (!$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
                
                // Also check organization_staff table directly
                $directOrgIds = \Illuminate\Support\Facades\DB::table('organization_staff')
                    ->where('staff_id', $staffRecord->id)
                    ->pluck('organization_id');
                
                if ($directOrgIds->isNotEmpty()) {
                    $directOrgs = Organization::whereIn('id', $directOrgIds)->get();
                    foreach ($directOrgs as $org) {
                        if (!$userOrganizations->contains('id', $org->id)) {
                            $userOrganizations->push($org);
                        }
                    }
                }
            }
        }
        
        // Get organization_id from request if provided
        $selectedOrganizationId = $request->query('organization_id');
        
        return view('admin.staff.dashboard.StudentOrgModerator.create-event', compact('userOrganizations', 'selectedOrganizationId'));
    }

    public function show(Event $event)
    {
    return view('admin.staff.dashboard.StudentOrgModerator.event-details', compact('event'));
    }
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // If admin is viewing, check for staff email or ID in query parameter
        $targetStaff = null;
        $targetUser = null;
        
        if ($isAdmin && $request->has('staff_email')) {
            // Admin viewing a specific staff member's dashboard
            $targetStaff = \App\Models\Staff::with(['organizations', 'organization'])->where('email', $request->staff_email)->first();
            if ($targetStaff) {
                $targetUser = \App\Models\User::where('email', $targetStaff->email)->first();
            }
        } elseif ($isAdmin && $request->has('staff_id')) {
            // Admin viewing a specific staff member's dashboard by ID
            $targetStaff = \App\Models\Staff::with(['organizations', 'organization'])->find($request->staff_id);
            if ($targetStaff) {
                $targetUser = \App\Models\User::where('email', $targetStaff->email)->first();
            }
        }
        
        // Use target staff/user if admin is viewing, otherwise use current user
        $staff = $targetStaff ?? \App\Models\Staff::with(['organizations', 'organization'])->where('email', $currentUser->email)->first();
        $user = $targetUser ?? $currentUser;
        $targetUserId = $user ? $user->id : auth()->id();
        
        // Access control: Allow all staff (role 2) and admins (role 4) to access events
        if (!$isAdmin && (int) $currentUser->role !== 2) {
            abort(403, 'Unauthorized: Only staff members can access this dashboard.');
        }
        
        $userOrganizations = collect();
        
        if ($staff) {
            // Get single organization from organization_id (if set)
            if ($staff->organization_id) {
                $org = $staff->organization ?? Organization::find($staff->organization_id);
                if ($org) {
                    $userOrganizations->push($org);
                }
            }
            
            // Get additional organizations from many-to-many relationship (organization_staff table)
            $additionalOrgs = $staff->organizations()->get();
            foreach ($additionalOrgs as $org) {
                // Avoid duplicates
                if (!$userOrganizations->contains('id', $org->id)) {
                    $userOrganizations->push($org);
                }
            }
        }
        
        // Also check if user has organizations directly (for staff who are users)
        if ($user && $user->organization_id) {
            $org = Organization::find($user->organization_id);
            if ($org && !$userOrganizations->contains('id', $org->id)) {
                $userOrganizations->push($org);
            }
        }
        
        // Get additional organizations from organization_user table
        if ($user && method_exists($user, 'otherOrganizations')) {
            $userOrgs = $user->otherOrganizations()->get();
            foreach ($userOrgs as $org) {
                if (!$userOrganizations->contains('id', $org->id)) {
                    $userOrganizations->push($org);
                }
            }
        }
        
        // Get all events for this user, grouped by organization
        $events = Event::where('created_by', $targetUserId)
            ->where('organization_id', '!=', null)
            ->with('organization')
            ->orderByDesc('event_date')
            ->get();
        
        // Pre-compute student counts for each organization
        $userOrganizations->each(function($org) {
            $orgDepartmentId = $org->department_id;
            
            if ($orgDepartmentId) {
                // If organization is department-related (academic), include ALL students from that department
                // Match students' department_id (from users table) with organization's department_id (from organizations table)
                // This makes students under Information Technology automatically belong to Student Council of Information Technology
                $allStudents = \App\Models\User::where('role', 1)
                    ->whereNotNull('department_id')
                    ->where('department_id', $orgDepartmentId)
                    ->get();
            } else {
                // For non-academic organizations (no department_id), only count explicit assignments
            $directStudents = $org->users()->where('role', 1)->get();
            $pivotStudents = $org->otherUsers()->where('role', 1)->get();
            $allStudents = $directStudents->merge($pivotStudents)->unique('id');
            }
            
            $org->studentCount = $allStudents->count();
        });
            
        return view('admin.staff.dashboard.StudentOrgModerator.index', compact('userOrganizations', 'events'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // Access control: Allow all staff (role 2) and admins (role 4) to create events
        if (!$isAdmin && (int) $currentUser->role !== 2) {
            abort(403, 'Unauthorized: Only staff members can create events.');
        }
        
        $request->validate([
            'title' => 'required|string|max:200',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'organization_id' => 'required|exists:organizations,id',
            'event_files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls,csv,txt|max:10240', // 10MB per file
        ]);

        // Verify that the staff member is assigned to the organization (unless admin)
        if (!$isAdmin) {
            $staff = \App\Models\Staff::with(['organizations', 'organization'])->where('email', $currentUser->email)->first();
            $user = $currentUser;
            
            $userOrganizations = collect();
            
            // Get organizations from Staff record if it exists
            if ($staff) {
                if ($staff->organization_id) {
                    $org = $staff->organization ?? Organization::find($staff->organization_id);
                    if ($org) {
                        $userOrganizations->push($org);
                    }
                }
                
                $additionalOrgs = $staff->organizations()->get();
                foreach ($additionalOrgs as $org) {
                    if (!$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
            }
            
            // Also check if user has organizations directly
            if ($user && $user->organization_id) {
                $org = Organization::find($user->organization_id);
                if ($org && !$userOrganizations->contains('id', $org->id)) {
                    $userOrganizations->push($org);
                }
            }
            
            // Get additional organizations from organization_user table
            if ($user && method_exists($user, 'otherOrganizations')) {
                $userOrgs = $user->otherOrganizations()->get();
                foreach ($userOrgs as $org) {
                    if (!$userOrganizations->contains('id', $org->id)) {
                        $userOrganizations->push($org);
                    }
                }
            }
            
            // Check organization_staff table directly if needed
            if ($staff && $userOrganizations->isEmpty()) {
                $directOrgIds = \Illuminate\Support\Facades\DB::table('organization_staff')
                    ->where('staff_id', $staff->id)
                    ->pluck('organization_id');
                
                if ($directOrgIds->isNotEmpty()) {
                    $directOrgs = Organization::whereIn('id', $directOrgIds)->get();
                    foreach ($directOrgs as $org) {
                        if (!$userOrganizations->contains('id', $org->id)) {
                            $userOrganizations->push($org);
                        }
                    }
                }
            }
            
            // If no staff record found but user is staff, try to find by user_id
            if (!$staff && $user && (int) $user->role === 2) {
                if ($user->user_id) {
                    $staffByUserId = \App\Models\Staff::where('user_id', $user->user_id)
                        ->with(['organizations', 'organization'])
                        ->first();
                    
                    if ($staffByUserId) {
                        if ($staffByUserId->organization_id) {
                            $org = Organization::find($staffByUserId->organization_id);
                            if ($org && !$userOrganizations->contains('id', $org->id)) {
                                $userOrganizations->push($org);
                            }
                        }
                        
                        $additionalOrgs = $staffByUserId->organizations()->get();
                        foreach ($additionalOrgs as $org) {
                            if (!$userOrganizations->contains('id', $org->id)) {
                                $userOrganizations->push($org);
                            }
                        }
                    }
                }
            }
            
            // Check if the requested organization is in the user's assigned organizations
            $requestedOrgId = (int) $request->organization_id;
            $hasAccess = $userOrganizations->contains('id', $requestedOrgId);
            
            if (!$hasAccess) {
                abort(403, 'Unauthorized: You are not assigned to this organization.');
            }
        }

        // Normalize time values to HH:MM:SS for MySQL strict mode
        $startTime = $request->start_time ?? '00:00';
        $endTime = $request->end_time ?? '23:59';
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
        $endDateTime = $endDate . ' ' . ($endTime ?: '23:59:59');

        // Create event first (without QR code)
        $event = Event::create([
            'name' => $request->title,
            'event_date' => $startDate, // Use start_date as event_date
            'end_date' => $endDate,
            'location' => $request->location,
            'description' => $request->description,
            'organization_id' => $request->organization_id,
            'created_by' => auth()->id(),
            'status' => 'pending',
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'qr_code_path' => '', // will update after QR code is generated
        ]);

        // Generate QR code for attendance (handle errors gracefully)
        try {
            // Use request host for mobile accessibility (works with local IP like 192.168.x.x)
            $baseUrl = request()->getSchemeAndHttpHost();
            $qrData = $baseUrl . '/admin/staff/dashboard/StudentOrgModerator/event-management?event_id=' . $event->id;
            $qrFileName = 'event_qr_' . $event->id . '.svg';
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($qrData);
            \Illuminate\Support\Facades\Storage::disk('public')->put("qrcodes/{$qrFileName}", $svg);
            $event->update(['qr_code_path' => 'storage/qrcodes/' . $qrFileName]);
        } catch (\Exception $e) {
            Log::error('QR code generation failed for event ID ' . $event->id . ': ' . $e->getMessage());
            // Event is still created, but no QR code
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
        
        return redirect()->route('admin.staff.dashboard.StudentOrgModerator.view-events')
            ->with('success', 'Event created! Awaiting approval.');
    }

    public function edit(Event $event)
    {
        $organizations = Organization::orderBy('name')->get();
        return view('admin.staff.dashboard.StudentOrgModerator.edit', compact('event', 'organizations'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'event_date' => 'required|date|after:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'location' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        // Combine date and time for start_time and end_time
        $startDateTime = $request->event_date . ' ' . $request->start_time;
        $endDateTime = $request->event_date . ' ' . $request->end_time;

        $event->update([
            'name' => $request->title,
            'event_date' => $request->event_date,
            'end_date' => $request->end_date,
            'location' => $request->location,
            'description' => $request->description,
            'organization_id' => $request->organization_id,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
        ]);

        // Regenerate QR code for attendance after update
        try {
            // Use request host for mobile accessibility (works with local IP like 192.168.x.x)
            $baseUrl = request()->getSchemeAndHttpHost();
            $qrData = $baseUrl . '/admin/staff/dashboard/StudentOrgModerator/event/' . $event->id . '/attendance';
            $qrFileName = 'event_qr_' . $event->id . '.svg';
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($qrData);
            \Illuminate\Support\Facades\Storage::disk('public')->put("qrcodes/{$qrFileName}", $svg);
            $event->update(['qr_code_path' => 'storage/qrcodes/' . $qrFileName]);
        } catch (\Exception $e) {
            Log::error('QR code regeneration failed for event ID ' . $event->id . ': ' . $e->getMessage());
            // Event is still updated, but no QR code
        }
        return redirect()->route('admin.staff.dashboard.StudentOrgModerator.view-events')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.staff.dashboard.StudentOrgModerator.view-events')
            ->with('success', 'Event deleted successfully.');
    }
    // ...existing code...
    public function qrcode(Event $event)
    {
        // Generate QR code data (URL or event info)
        // Use request host for mobile accessibility (works with local IP like 192.168.x.x)
        $baseUrl = request()->getSchemeAndHttpHost();
        $qrData = $baseUrl . '/admin/staff/dashboard/StudentOrgModerator/event/' . $event->id . '/attendance';
        // Use SVG format (doesn't require imagick extension)
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($qrData);
        return view('admin.staff.dashboard.StudentOrgModerator.qrcode', compact('event', 'qrCode'));
    }
}
