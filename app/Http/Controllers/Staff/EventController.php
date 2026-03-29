<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notifications\EventApprovedNotification;
use App\Mail\EventApprovedMail;
use App\Mail\EventDeclinedMail;

class EventController extends Controller
{
    public function create()
    {
        return view('staff.events.create');
    }
    // Staff event index: show events created by this staff
    public function index()
    {
        $staff = auth()->user();
        $events = \App\Models\Event::where('created_by', $staff->id)
            ->withCount('participants')
            ->latest('event_date')
            ->get();

        // Pending events for approval (status = 'pending')
        $pendingEvents = \App\Models\Event::where('status', 'pending')
            ->with('creator')
            ->latest('event_date')
            ->get();

        // Recent participants (for quick view)
        $participants = \App\Models\EventParticipant::whereHas('event', function($q) use ($staff) {
                $q->where('created_by', $staff->id);
            })
            ->with('user', 'event')
            ->latest()
            ->take(10)
            ->get();

        return view('staff.events.index', compact('events', 'pendingEvents', 'participants'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'event_date' => 'required|date|after:today',
            'location' => 'nullable|string|max:200',
            'description' => 'nullable|string',
        ]);

        $event = \App\Models\Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'created_by' => auth()->id(),
            'status' => 'pending',
        ]);

        // Add default requirements
        $requirements = ['Parent Consent', 'ID Picture', 'Registration Form'];
        foreach ($requirements as $req) {
            \App\Models\EventRequirement::create([
                'event_id' => $event->id,
                'requirement_name' => $req,
            ]);
        }

        return redirect()->back()->with('success', 'Event created! Awaiting admin approval.');
    }

    public function uploadFile($id, Request $request)
    {
        $event = \App\Models\Event::findOrFail($id);
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xlsx,xls|max:10240', // 10MB
        ]);
        
        $file = $request->file('file');
        
        // Sanitize filename
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        $filename = time() . '_' . $filename; // Add timestamp to prevent conflicts
        
        // Use event ID instead of title for security
        $path = $file->storeAs('events/' . $event->id, $filename, 'public');
        
        \App\Models\EventRequirement::create([
            'event_id' => $event->id,
            'requirement_name' => $file->getClientOriginalName(), // Store original name
            'file_path' => $path,
        ]);
        
        return back()->with('success', 'File uploaded.');
    }

    public function downloadFile($id, $file)
    {
        $event = \App\Models\Event::findOrFail($id);
        
        // Sanitize filename to prevent path traversal
        $file = basename($file);
        
        // Use event ID instead of title for path to prevent directory traversal
        $path = storage_path('app/public/events/' . $event->id . '/' . $file);
        
        // Verify file exists and is within the allowed directory
        if (!file_exists($path)) {
            abort(404);
        }
        
        // Additional security: verify path is within expected directory
        $realPath = realpath($path);
        $basePath = realpath(storage_path('app/public/events/' . $event->id));
        
        if (!$realPath || !$basePath || !str_starts_with($realPath, $basePath)) {
            abort(404);
        }
        
        return response()->download($realPath, $file);
    }

    public function history(Request $request)
    {
        $query = \App\Models\Event::where('created_by', auth()->id());
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
            $query->where('year_level', $request->year_level);
        }
        $events = $query->orderBy('event_date', 'desc')->paginate(10);
        $departments = \App\Models\Department::all();
        $courses = \App\Models\Course::all();
        return view('staff.events-history', compact('events', 'departments', 'courses'));
    }

    public function approve($id)
    {
        $event = \App\Models\Event::with('organization')->findOrFail($id);
        
        // Check if staff can approve this event (must be admin or have appropriate permissions)
        // For now, allow staff to approve events (can be restricted later via policy)
        $event->update(['status' => 'approved']);
        
        // Notify event creator
        if ($event->created_by) {
            $creator = \App\Models\User::find($event->created_by);
            if ($creator) {
                $creator->notify(new EventApprovedNotification($event));
            }
        }
        
        // Send email to organization's official email
        if ($event->organization && $event->organization->official_email) {
            try {
                \Illuminate\Support\Facades\Mail::to($event->organization->official_email)
                    ->send(new EventApprovedMail($event));
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

    public function decline(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:1000',
        ]);

        $event = \App\Models\Event::with('organization')->findOrFail($id);
        
        // Check if staff can decline this event (must be admin or have appropriate permissions)
        // For now, allow staff to decline events (can be restricted later via policy)
        $event->update([
            'status' => 'declined',
            'decline_reason' => $request->reason,
        ]);
        
        // Send email to organization's official email
        if ($event->organization && $event->organization->official_email) {
            try {
                \Illuminate\Support\Facades\Mail::to($event->organization->official_email)
                    ->send(new EventDeclinedMail($event));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send event decline email to organization', [
                    'event_id' => $event->id,
                    'organization_id' => $event->organization->id,
                    'email' => $event->organization->official_email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return back()->with('success', 'Event declined and notifications sent.');
    }
}