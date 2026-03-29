<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MarkAbsentParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:mark-absent-participants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark unscanned event participants as Absent after the absent threshold and notify staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for events with participants to mark as Absent...');

        // Find events where monitoring has started and absent threshold has passed
        $now = now();
        $events = Event::where('required_student_participation', true)
            ->where('monitoring_started', true)
            ->whereNotNull('monitoring_started_at')
            ->whereNotNull('absent_threshold_minutes')
            ->get();

        $totalMarked = 0;

        foreach ($events as $event) {
            $referenceTime = $event->monitoring_started_at ?? $event->start_time;
            
            if (!$referenceTime) {
                continue;
            }

            $absentThreshold = $event->absent_threshold_minutes ?? 120;
            $thresholdTime = Carbon::parse($referenceTime)->addMinutes($absentThreshold);

            // Only process if threshold has passed
            if ($now->lt($thresholdTime)) {
                continue;
            }

            // Find all participants who haven't been scanned or marked yet (only NULL attendance_status)
            // Only mark participants with NULL status as Absent - don't overwrite 'Attended' or 'Late' statuses
            $unscannedParticipants = EventParticipant::where('event_id', $event->id)
                ->where(function($query) {
                    $query->where('qr_scanned', false)
                          ->orWhereNull('qr_scanned')
                          ->orWhere(function($q) {
                              $q->whereNotNull('qr_scanned')
                                ->where('qr_scanned', true)
                                ->whereNull('attendance_status');
                          });
                })
                ->whereNull('attendance_status')
                ->get();

            if ($unscannedParticipants->isEmpty()) {
                continue;
            }

            // Mark as Absent
            $markedCount = 0;
            $absentStudents = [];

            foreach ($unscannedParticipants as $participant) {
                $participant->update([
                    'attendance_status' => 'Absent',
                ]);
                $markedCount++;

                // Get student info for notification
                $student = User::find($participant->user_id);
                if ($student) {
                    $absentStudents[] = [
                        'name' => "{$student->first_name} {$student->last_name}",
                        'student_id' => $student->id ?? 'N/A',
                        'email' => $student->email ?? 'N/A',
                    ];
                }
            }

            $totalMarked += $markedCount;

            $this->info("Event: {$event->name} - Marked {$markedCount} participants as Absent");

            // Notify event creator and admin staff
            if (!empty($absentStudents)) {
                $this->notifyAbsentStudents($event, $absentStudents);
            }
        }

        if ($totalMarked > 0) {
            $this->info("Total participants marked as Absent: {$totalMarked}");
        } else {
            $this->info('No participants needed to be marked as Absent.');
        }

        return Command::SUCCESS;
    }

    /**
     * Notify staff about absent students
     */
    private function notifyAbsentStudents($event, $absentStudents)
    {
        try {
            // Get event creator
            $creator = User::find($event->created_by);
            $recipients = [];

            if ($creator && $creator->email) {
                $recipients[] = $creator->email;
            }

            // Get organization coordinator email if available
            if ($event->organization && $event->organization->official_email) {
                $recipients[] = $event->organization->official_email;
            }

            // Send notification to each recipient
            foreach (array_unique($recipients) as $email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($email)->queue(
                        new \App\Mail\AbsentStudentsNotification($event, $absentStudents)
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send absent students notification', [
                        'event_id' => $event->id,
                        'email' => $email,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in notifyAbsentStudents', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
