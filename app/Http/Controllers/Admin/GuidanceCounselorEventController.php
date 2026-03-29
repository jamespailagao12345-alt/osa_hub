<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class GuidanceCounselorEventController extends Controller
{
    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // Access control: If not admin, verify that the user is a Guidance Counselor
        if (!$isAdmin) {
            $staff = \App\Models\Staff::where('email', $currentUser->email)->first();
            $userDesignation = $currentUser->designation
                ?? optional($currentUser->staffProfile)->designation
                ?? ($staff ? $staff->designation : null);
            
            // Normalize designation to handle both spellings (standardize on American spelling)
            $normalizedDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
                $normalizedDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can create events.');
            }
        }
        
        // Use GuidanceCounselor (American spelling) to match the directory name
        return view('admin.staff.dashboard.GuidanceCounselor.create-event');
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // Access control: If not admin, verify that the user is a Guidance Counselor
        if (!$isAdmin) {
            $staff = \App\Models\Staff::where('email', $currentUser->email)->first();
            $userDesignation = $currentUser->designation
                ?? optional($currentUser->staffProfile)->designation
                ?? ($staff ? $staff->designation : null);
            
            // Normalize designation to handle both spellings (standardize on American spelling)
            $normalizedDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
                $normalizedDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can create events.');
            }
        }
        
        $request->validate([
            'title' => 'required|string|max:200',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'event_files.*' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls,csv,txt|max:10240', // 10MB per file
        ]);

        // Get Guidance Counselor name
        $coordinatorName = 'Guidance Counselor';
        $fullName = trim(($currentUser->first_name ?? '') . ' ' . ($currentUser->last_name ?? ''));
        if (!empty($fullName)) {
            $coordinatorName = 'Guidance Counselor — ' . $fullName;
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

        // Create event with organization_id as null and coordinator_name set
        $event = Event::create([
            'name' => $request->title,
            'event_date' => $startDate,
            'end_date' => $endDate,
            'location' => $request->location,
            'description' => $request->description,
            'organization_id' => null,
            'coordinator_name' => $coordinatorName,
            'created_by' => auth()->id(),
            'status' => 'pending',
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'qr_code_path' => '', // will update after QR code is generated
        ]);

        // Generate QR code for attendance (handle errors gracefully)
        try {
            $baseUrl = request()->getSchemeAndHttpHost();
            // Use the general admin event show route for QR code
            $qrData = $baseUrl . '/admin/events/' . $event->id;
            $qrFileName = 'event_qr_' . $event->id . '.svg';
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(300)->generate($qrData);
            \Illuminate\Support\Facades\Storage::disk('public')->put("qrcodes/{$qrFileName}", $svg);
            $event->update(['qr_code_path' => 'storage/qrcodes/' . $qrFileName]);
        } catch (\Exception $e) {
            Log::error('QR code generation failed for event ID ' . $event->id . ': ' . $e->getMessage());
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
        
        // Use American spelling for redirect
        $redirectDesignation = 'Guidance Counselor';
        return redirect()->route('admin.staff.dashboard.designation', ['designation' => $redirectDesignation])
            ->with('success', 'Event created! Awaiting approval.');
    }

    public function pendingEvents(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // Access control: If not admin, verify that the user is a Guidance Counselor
        if (!$isAdmin) {
            $staff = \App\Models\Staff::where('email', $currentUser->email)->first();
            $userDesignation = $currentUser->designation
                ?? optional($currentUser->staffProfile)->designation
                ?? ($staff ? $staff->designation : null);
            
            $normalizedDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
                $normalizedDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can access this page.');
            }
        }
        
        $events = Event::where('created_by', $currentUser->id)
            ->where('status', 'pending')
            ->whereNull('organization_id')
            ->whereNotNull('coordinator_name')
            ->with('creator')
            ->orderBy('event_date', 'asc')
            ->paginate(15);
        
        return view('admin.staff.dashboard.GuidanceCounselor.pending-events', compact('events'));
    }

    public function approvedEvents(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // Access control: If not admin, verify that the user is a Guidance Counselor
        if (!$isAdmin) {
            $staff = \App\Models\Staff::where('email', $currentUser->email)->first();
            $userDesignation = $currentUser->designation
                ?? optional($currentUser->staffProfile)->designation
                ?? ($staff ? $staff->designation : null);
            
            $normalizedDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
                $normalizedDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can access this page.');
            }
        }
        
        $events = Event::where('created_by', $currentUser->id)
            ->where('status', 'approved')
            ->whereNull('organization_id')
            ->whereNotNull('coordinator_name')
            ->with('creator')
            ->orderBy('event_date', 'asc')
            ->paginate(15);
        
        return view('admin.staff.dashboard.GuidanceCounselor.approved-events', compact('events'));
    }

    public function eventsHistory(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser && (int) $currentUser->role === 4;
        
        // Access control: If not admin, verify that the user is a Guidance Counselor
        if (!$isAdmin) {
            $staff = \App\Models\Staff::where('email', $currentUser->email)->first();
            $userDesignation = $currentUser->designation
                ?? optional($currentUser->staffProfile)->designation
                ?? ($staff ? $staff->designation : null);
            
            $normalizedDesignation = trim($userDesignation ?? '');
            if (strcasecmp($normalizedDesignation, 'Guidance Counsellor') === 0) {
                $normalizedDesignation = 'Guidance Counselor';
            }
            
            if (!$userDesignation || strcasecmp($normalizedDesignation, 'Guidance Counselor') !== 0) {
                abort(403, 'Unauthorized: Only Guidance Counselor can access this page.');
            }
        }
        
        $events = Event::where('created_by', $currentUser->id)
            ->whereNull('organization_id')
            ->whereNotNull('coordinator_name')
            ->with('creator')
            ->orderBy('event_date', 'desc')
            ->paginate(15);
        
        return view('admin.staff.dashboard.GuidanceCounselor.events-history', compact('events'));
    }
}

