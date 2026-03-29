<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function approve($id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $this->authorize('approve', $appointment);
        $from = $appointment->status;
        $appointment->status = 'approved';
        $appointment->save();
        \App\Models\StatusChange::create([
            'auditable_type' => \App\Models\Appointment::class,
            'auditable_id' => $appointment->id,
            'from_status' => $from,
            'to_status' => 'approved',
            'changed_by' => auth()->id(),
            'meta' => null,
        ]);
        // Send notification email (queue it to avoid blocking)
        if (!empty($appointment->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->email)->queue(new \App\Mail\AppointmentApprovedMail($appointment));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue appointment approved email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return redirect()->back()->with('success', 'Appointment approved and user notified.');
    }

    public function decline($id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $this->authorize('decline', $appointment);
        $from = $appointment->status;
        $appointment->status = 'declined';
        $appointment->save();
        \App\Models\StatusChange::create([
            'auditable_type' => \App\Models\Appointment::class,
            'auditable_id' => $appointment->id,
            'from_status' => $from,
            'to_status' => 'declined',
            'changed_by' => auth()->id(),
            'meta' => null,
        ]);
        // Send notification email (queue it to avoid blocking)
        if (!empty($appointment->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->email)->queue(new \App\Mail\AppointmentDeclinedMail($appointment));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to queue appointment declined email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        return redirect()->back()->with('success', 'Appointment declined and user notified.');
    }

    public function reschedule(Request $request, $id)
    {
        $appointment = \App\Models\Appointment::findOrFail($id);
        $this->authorize('reschedule', $appointment);
        $from = $appointment->status;
        $appointment->status = 'rescheduled';
        $appointment->appointment_date = $request->appointment_date;
        $appointment->appointment_time = $request->appointment_time;
        $appointment->save();
        \App\Models\StatusChange::create([
            'auditable_type' => \App\Models\Appointment::class,
            'auditable_id' => $appointment->id,
            'from_status' => $from,
            'to_status' => 'rescheduled',
            'changed_by' => auth()->id(),
            'meta' => null,
        ]);
        // Send notification email
        if (!empty($appointment->email)) {
            Mail::to($appointment->email)->send(new \App\Mail\AppointmentRescheduledMail($appointment));
        }
        return redirect()->back()->with('success', 'Appointment rescheduled and user notified.');
    }
    // Staff view: appointments assigned to this staff
    public function staffIndex()
    {
        $staff = auth()->user();
        $appointments = \App\Models\Appointment::where('assigned_staff_id', $staff->id)
            ->with('user')
            ->latest('appointment_date')
            ->get();
        return view('staff.appointments.index', compact('appointments'));
    }
    public function index()
    {
        // If student is viewing all appointments
        if (request()->route()->getName() === 'student.appointments.index') {
            $appointments = \App\Models\Appointment::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhere('email', auth()->user()->email);
            })->with('assignedStaff')->orderByDesc('appointment_date')->get();
            return view('student.appointments.index', compact('appointments'));
        }
        // Otherwise, show make-appointment form
        $concerns = \App\Models\Staff::query()
            ->whereNotNull('designation')
            ->pluck('designation')
            ->unique()
            ->values();

        // Fetch booked slots for disabling
        $bookedSlots = \App\Models\Appointment::where('status', 'pending')
            ->orWhere('status', 'approved')
            ->get(['appointment_date', 'appointment_time'])
            ->map(function($appt) {
                return [
                    'date' => $appt->appointment_date->format('Y-m-d'),
                    'time' => $appt->appointment_time
                ];
            })->toArray();

        // Calculate fully booked dates (all 16 time slots are taken: 8:00 AM to 3:00 PM in 30-min intervals)
        $allowedTimes = [];
        for ($hour = 8; $hour <= 15; $hour++) {
            foreach ([0, 30] as $minute) {
                $allowedTimes[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }
        $totalTimeSlots = count($allowedTimes); // 16 slots
        
        // Group booked slots by date
        $bookedByDate = [];
        foreach ($bookedSlots as $slot) {
            if (!isset($bookedByDate[$slot['date']])) {
                $bookedByDate[$slot['date']] = 0;
            }
            $bookedByDate[$slot['date']]++;
        }
        
        // Find dates where all slots are booked
        $fullyBookedDates = [];
        foreach ($bookedByDate as $date => $count) {
            if ($count >= $totalTimeSlots) {
                $fullyBookedDates[] = $date;
            }
        }

        // Check if this is the student.make-appointment route
        if (request()->route()->getName() === 'student.make-appointment') {
            // Get only non-academic organizations for the modal
            $nonAcademicOrganizations = \App\Models\Organization::whereNull('department_id')
                ->orderBy('name')
                ->get();
            return view('student.make-appointment', compact('concerns', 'bookedSlots', 'nonAcademicOrganizations', 'fullyBookedDates'));
        }

        return view('make-appointment', compact('concerns', 'bookedSlots', 'fullyBookedDates'));
    }
    public function store(Request $request)
    {
        // Validate input - check for Guidance Counselor (handle both American and British spellings)
        $isGuidanceCounselor = $request->concern && (
            stripos($request->concern, 'Guidance') !== false && 
            (stripos($request->concern, 'Counselor') !== false || stripos($request->concern, 'Counsellor') !== false)
        );
        
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:150',
            'email' => 'required|email',
            'contact_number' => 'required|string|max:20',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required',
            'concern' => 'required|string',
            'reason_for_counseling' => $isGuidanceCounselor ? 'required|string' : 'nullable|string',
            'category' => $isGuidanceCounselor ? 'required|string|in:Red,Blue,Yellow' : 'nullable|string|in:Red,Blue,Yellow',
        ]);

        // Custom backend validation
        $date = $request->appointment_date;
        $time = $request->appointment_time;
        // Note: Weekend validation is handled by the calendar UI, allowing only weekday selection
        $allowedTimes = [];
        for ($hour = 8; $hour <= 15; $hour++) {
            foreach ([0, 30] as $minute) {
                $allowedTimes[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }
        if (!in_array($time, $allowedTimes)) {
            return redirect()->back()->withErrors(['appointment_time' => 'Please select a valid time between 8:00 AM and 3:00 PM in 30-minute intervals.'])->withInput();
        }
        $alreadyBooked = \App\Models\Appointment::where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
        if ($alreadyBooked) {
            return redirect()->back()->withErrors(['appointment_time' => 'This date and time is already booked. Please choose another slot.'])->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Find staff with the selected designation to assign
        $concern = $request->concern;
        $assignedStaffId = null;
        
        // First check Staff table, then find corresponding User by email
        $staffRecord = \App\Models\Staff::where('designation', $concern)->first();
        if ($staffRecord) {
            // Find the User record by email
            $staff = \App\Models\User::where('email', $staffRecord->email)->first();
            $assignedStaffId = $staff?->id;
        }
        // Fallback: also check User table directly (in case designation is set there)
        if (!$assignedStaffId) {
            $staff = \App\Models\User::where('designation', $concern)->first();
            $assignedStaffId = $staff?->id;
        }

        // Save appointment with concern and additional fields
        $appointment = \App\Models\Appointment::create([
            'user_id' => auth()->id(),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'concern' => $concern,
            'reason_for_counseling' => $request->reason_for_counseling ?? null,
            'category' => $request->category ?? null,
            'assigned_staff_id' => $assignedStaffId,
            'status' => 'pending',
        ]);

        // Send notification email to sender (queue it to avoid blocking)
        if (!empty($appointment->email)) {
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->email)->queue(new \App\Mail\AppointmentSubmittedMail($appointment));
            } catch (\Throwable $e) {
                // Log error but don't fail the request if email fails
                \Illuminate\Support\Facades\Log::error('Failed to queue appointment submitted email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (auth()->check()) {
            return redirect()->back()->with('success', 'Appointment request submitted!');
        } else {
            return redirect('/')->with('success', 'Appointment request submitted!');
        }
    }
}
// --- Admin actions for appointments ---