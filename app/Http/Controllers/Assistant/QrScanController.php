<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class QrScanController extends Controller
{
    /**
     * Display QR code scanner page
     */
    public function index()
    {
        // Get events with required_student_participation enabled
        $events = Event::where('required_student_participation', true)
            ->where('status', 'approved')
            ->orderBy('start_time', 'desc')
            ->get();
        
        return view('assistant.qrscan', compact('events'));
    }

    /**
     * Scan QR code and save as event participation
     * Available to all student leaders (role 3) for events with required_student_participation ON
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'event_id' => 'required|exists:events,id',
        ]);

        try {
            // Parse QR code data (JSON format)
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format. Please scan a valid student QR code.'
                ], 400);
            }

            // Extract student information from QR data
            // QR code can have student_id (which is usually the user's id) or id
            $studentId = $qrData['student_id'] ?? $qrData['id'] ?? null;
            $firstName = $qrData['first_name'] ?? null;
            $lastName = $qrData['last_name'] ?? null;
            $middleName = $qrData['middle_name'] ?? null;

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID not found in QR code.'
                ], 400);
            }

            // Try multiple lookup strategies
            // 1. First try by id field (student_id in QR is usually the user's id)
            $user = User::where('id', $studentId)->first();
            
            // 2. If not found, try by user_id field (string field for student/staff ID)
            if (!$user) {
                $user = User::where('user_id', $studentId)->first();
            }
            
            // 3. If still not found, try by user_id as string (in case of type mismatch)
            if (!$user) {
                $user = User::where('user_id', (string) $studentId)->first();
            }
            
            // 4. Fallback: try to find by name (case-insensitive, trimmed)
            if (!$user && $firstName && $lastName) {
                $user = User::whereRaw('LOWER(TRIM(first_name)) = ?', [strtolower(trim($firstName))])
                    ->whereRaw('LOWER(TRIM(last_name)) = ?', [strtolower(trim($lastName))])
                    ->first();
                
                // If middle name is provided, use it for more precise matching
                if (!$user && $middleName) {
                    $user = User::whereRaw('LOWER(TRIM(first_name)) = ?', [strtolower(trim($firstName))])
                        ->whereRaw('LOWER(TRIM(middle_name)) = ?', [strtolower(trim($middleName))])
                        ->whereRaw('LOWER(TRIM(last_name)) = ?', [strtolower(trim($lastName))])
                        ->first();
                }
            }

            if (!$user) {
                Log::warning('QR scan: Student not found', [
                    'student_id' => $studentId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'qr_data' => $qrData
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found in database. Please ensure the student is registered. Student ID: ' . $studentId
                ], 404);
            }
            
            // Check if user is a student (role 1), staff (role 2), or student leader (role 3) - all can be scanned
            $userRole = (int) $user->role;
            if (!in_array($userRole, [1, 2, 3])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This QR code belongs to an unsupported account type. Only student, staff, and student leader QR codes can be scanned for event participation.'
                ], 400);
            }
            
            // Determine participant type label
            $participantType = 'Student';
            if ($userRole === 2) {
                $participantType = 'Staff';
            } elseif ($userRole === 3) {
                $participantType = 'Student Leader';
            }

            // Verify event exists, is accessible, and has Required Student Participation enabled
            $event = Event::findOrFail($request->event_id);
            
            // Only allow scanning for events with Required Student Participation ON
            if (!$event->required_student_participation) {
                return response()->json([
                    'success' => false,
                    'message' => 'This event does not require student participation tracking.'
                ], 400);
            }

            // Calculate attendance status if monitoring is active
            $scannedAt = now();
            $attendanceStatus = null;
            if ($event->monitoring_started) {
                $attendanceStatus = \App\Http\Controllers\Admin\DashboardController::calculateAttendanceStatus($event, $scannedAt);
            }

            // Check if participation already exists
            $existingParticipation = EventParticipant::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingParticipation) {
                // Update existing participation with QR scan info
                $updateData = [
                    'qr_scanned' => true,
                    'scanned_at' => $scannedAt,
                    'scanned_by' => auth()->id(),
                ];
                
                if ($attendanceStatus) {
                    $updateData['attendance_status'] = $attendanceStatus;
                }
                
                $existingParticipation->update($updateData);

                $statusMessage = $attendanceStatus ? " ({$attendanceStatus})" : "";
                
                return response()->json([
                    'success' => true,
                    'message' => "{$user->first_name} {$user->last_name} ({$participantType}) is already registered for this event. QR scan updated.{$statusMessage}",
                    'student' => [
                        'id' => $user->id,
                        'name' => "{$user->first_name} {$user->last_name}",
                        'student_id' => $user->user_id,
                    ],
                    'participant_type' => $participantType,
                    'event' => [
                        'id' => $event->id,
                        'title' => $event->title ?? $event->name,
                    ],
                    'attendance_status' => $attendanceStatus,
                ]);
            }

            // Create new participation record
            $participationData = [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'qr_scanned' => true,
                'scanned_at' => $scannedAt,
                'scanned_by' => auth()->id(),
            ];
            
            if ($attendanceStatus) {
                $participationData['attendance_status'] = $attendanceStatus;
            }
            
            $participation = EventParticipant::create($participationData);

            Log::info('QR code scanned for event participation', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'scanned_by' => auth()->id(),
            ]);

            $eventTitle = $event->title ?? $event->name;
            $statusMessage = $attendanceStatus ? " ({$attendanceStatus})" : "";
            
            return response()->json([
                'success' => true,
                'message' => "{$user->first_name} {$user->last_name} ({$participantType}) has been registered for {$eventTitle}.{$statusMessage}",
                'student' => [
                    'id' => $user->id,
                    'name' => "{$user->first_name} {$user->last_name}",
                    'student_id' => $user->user_id,
                ],
                'participant_type' => $participantType,
                'event' => [
                    'id' => $event->id,
                    'title' => $eventTitle,
                ],
                'attendance_status' => $attendanceStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('QR scan error', [
                'error' => $e->getMessage(),
                'qr_data' => $request->qr_data,
                'event_id' => $request->event_id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the QR code: ' . $e->getMessage()
            ], 500);
        }
    }
}
