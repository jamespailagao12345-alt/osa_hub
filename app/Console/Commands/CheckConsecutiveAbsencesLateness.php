<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EventParticipant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckConsecutiveAbsencesLateness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:check-consecutive-absences-lateness';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for consecutive absences (3) and lateness (5) and suspend students accordingly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for consecutive absences and lateness...');

        // Get all students (role 1)
        $students = User::where('role', 1)->get();
        $suspendedCount = 0;

        foreach ($students as $student) {
            // Skip if already suspended
            if ($student->suspended) {
                continue;
            }

            // Get all event participations ordered by event start time (most recent first)
            // Join events table to properly order at database level and filter out events without start_time
            $participations = EventParticipant::where('event_participants.user_id', $student->id)
                ->whereNotNull('event_participants.attendance_status')
                ->join('events', 'event_participants.event_id', '=', 'events.id')
                ->where('events.status', 'approved')
                ->whereNotNull('events.start_time') // Exclude events without start_time for proper ordering
                ->select('event_participants.*')
                ->orderBy('events.start_time', 'desc')
                ->with('event')
                ->get();

            if ($participations->isEmpty()) {
                continue;
            }

            // Check for consecutive Absent (3) or Late (5) starting from most recent
            $consecutiveAbsent = 0;
            $consecutiveLate = 0;
            $shouldSuspend = false;
            $suspendReason = '';

            foreach ($participations as $participation) {
                if ($participation->attendance_status === 'Absent') {
                    $consecutiveAbsent++;
                    $consecutiveLate = 0; // Reset late counter when we see an Absent
                    
                    if ($consecutiveAbsent >= 3) {
                        $shouldSuspend = true;
                        $suspendReason = 'System imposed suspension: Please see the OSA Head';
                        break;
                    }
                } elseif ($participation->attendance_status === 'Late') {
                    $consecutiveLate++;
                    $consecutiveAbsent = 0; // Reset absent counter when we see a Late
                    
                    if ($consecutiveLate >= 5) {
                        $shouldSuspend = true;
                        $suspendReason = 'System imposed suspension: Please see the OSA Head';
                        break;
                    }
                } else {
                    // If status is "Attended", reset both counters (breaks the consecutive chain)
                    $consecutiveAbsent = 0;
                    $consecutiveLate = 0;
                }
            }

            if ($shouldSuspend) {
                // Suspend the student
                $student->update([
                    'suspended' => true,
                    'suspension_reason' => $suspendReason,
                ]);

                $suspendedCount++;

                $this->info("Suspended student: {$student->first_name} {$student->last_name} - {$suspendReason}");

                // Send notification to student
                try {
                    if ($student->email) {
                        Mail::to($student->email)->queue(
                            new \App\Mail\StudentSuspensionNotification($student, $suspendReason)
                        );
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send suspension notification', [
                        'student_id' => $student->id,
                        'email' => $student->email,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('Student suspended for consecutive absences/lateness', [
                    'student_id' => $student->id,
                    'reason' => $suspendReason,
                    'consecutive_absent' => $consecutiveAbsent,
                    'consecutive_late' => $consecutiveLate,
                ]);
            }
        }

        if ($suspendedCount > 0) {
            $this->info("Total students suspended: {$suspendedCount}");
        } else {
            $this->info('No students needed to be suspended.');
        }

        return Command::SUCCESS;
    }
}
