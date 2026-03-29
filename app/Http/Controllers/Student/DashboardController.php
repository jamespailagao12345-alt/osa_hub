<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Restore original QR code logic: generate SVG if missing
        $qrPath = "qr-codes/{$user->id}.svg";
        if (!Storage::disk('public')->exists($qrPath)) {
            $qrPayload = [
                'student_id' => $user->id,
                'first_name' => $user->first_name,
                'middle_name' => $user->middle_name ?? null,
                'last_name' => $user->last_name,
                'department' => optional($user->department)->name,
                'course' => optional($user->course)->name,
                'year_level' => optional($user->studentInformation)->year_level,
                'generated_at' => now()->toIso8601String(),
            ];
            $qrData = json_encode($qrPayload);
            $svg = QrCode::format('svg')->size(300)->generate($qrData);
            Storage::disk('public')->put($qrPath, $svg);
        }

        // Load data
        $appointments = \App\Models\Appointment::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('email', $user->email);
            })
            ->where('status', '!=', 'cancelled')
            ->with('assignedStaff')
            ->latest('appointment_date')
            ->take(3)
            ->get();

        $upcomingEvents = \App\Models\Event::where('event_date', '>=', today())
            ->where('status', 'approved')
            ->latest('event_date')
            ->get();

        $participations = \App\Models\EventParticipant::where('user_id', $user->id)
            ->with('event')
            ->latest()
            ->get();

        // Prepare calendar data for minimized view
        // Get month from request or use current month
        $monthParam = $request->get('month');
        if ($monthParam) {
            try {
                $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $selectedMonth = now()->startOfMonth();
            }
        } else {
            $selectedMonth = now()->startOfMonth();
        }
        
        $year = $selectedMonth->year;
        $calendarEvents = \App\Models\Event::where('status', 'approved')
            ->where(function($query) use ($year) {
                $query->whereYear('event_date', $year)
                      ->orWhereYear('event_date', $year + 1);
            })
            ->orderBy('event_date')
            ->get();
        
        // Group events by date for calendar display
        $eventsByDate = $calendarEvents->groupBy(function($event) {
            return \Carbon\Carbon::parse($event->event_date)->format('Y-m-d');
        });

    // Get only non-academic organizations (those without department_id)
    // Students are automatically members of their department's organization
    $nonAcademicOrganizations = \App\Models\Organization::whereNull('department_id')
        ->orderBy('name')
        ->get();
    $departments = \App\Models\Department::all();
    $scholarships = \App\Models\Scholarship::all();
    
    // Calculate total points earned by student
    $totalPoints = \App\Models\StudentPoint::where('user_id', $user->id)->sum('points');
    
    return view('student.dashboard', compact('appointments', 'upcomingEvents', 'participations', 'nonAcademicOrganizations', 'departments', 'scholarships', 'eventsByDate', 'year', 'selectedMonth', 'totalPoints'));
    }

    public function qrCode()
    {
        $user = auth()->user();
        
        // Create QR code payload with student information
        $qrPayload = [
            'student_id' => $user->id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name ?? null,
            'last_name' => $user->last_name,
            'department' => optional($user->department)->name,
            'course' => optional($user->course)->name,
            'year_level' => optional($user->studentInformation)->year_level,
            'generated_at' => now()->toIso8601String(),
        ];
        $qrData = json_encode($qrPayload);
        
        // Generate SVG QR code
        $svg = QrCode::format('svg')->size(300)->generate($qrData);
        
        // Return SVG with proper content type header
        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'inline; filename="student-qr-code.svg"');
    }
    
    public function events(Request $request)
    {
        $query = \App\Models\Event::where('status', 'approved');
        if ($request->filled('date')) {
            $query->whereDate('event_date', $request->date);
        }
        $events = $query->orderBy('event_date', 'desc')->paginate(10);
        return view('student.events', compact('events'));
    }

    public function exportParticipants(Request $request)
    {
        $user = auth()->user();
        // Limit to this student's participations
        $participants = \App\Models\EventParticipant::where('user_id', $user->id)
            ->with(['event'])
            ->orderByDesc('created_at')
            ->get();

        // Build a CSV response inline (no admin-only Excel dependency)
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_participants_'.now()->format('Ymd_His').'.csv"',
        ];

        $callback = function() use ($participants) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Event', 'Date', 'Status']);
            foreach ($participants as $p) {
                fputcsv($out, [
                    $p->event->title ?? 'N/A',
                    optional($p->event)->event_date,
                    $p->status ?? 'registered',
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Return calendar view only for AJAX requests
     */
    public function calendarView(Request $request)
    {
        $user = auth()->user();
        
        // Get month from request or use current month
        $monthParam = $request->get('month');
        if ($monthParam) {
            try {
                $selectedMonth = \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $selectedMonth = now()->startOfMonth();
            }
        } else {
            $selectedMonth = now()->startOfMonth();
        }
        
        $year = $selectedMonth->year;
        $calendarEvents = \App\Models\Event::where('status', 'approved')
            ->where(function($query) use ($year) {
                $query->whereYear('event_date', $year)
                      ->orWhereYear('event_date', $year + 1);
            })
            ->orderBy('event_date')
            ->get();
        
        // Group events by date for calendar display
        $eventsByDate = $calendarEvents->groupBy(function($event) {
            return \Carbon\Carbon::parse($event->event_date)->format('Y-m-d');
        });
        
        return view('student.partials.calendar-view', compact('eventsByDate', 'year', 'selectedMonth'));
    }
}