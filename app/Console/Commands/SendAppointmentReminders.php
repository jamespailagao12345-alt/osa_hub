<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Mail\AppointmentReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders to staff one hour before approved appointments and rescheduled appointments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for appointments that need reminders...');
        
        $now = Carbon::now();
        $remindersSent = 0;
        
        // Calculate time window: appointments that are exactly 1 hour from now (with 2-minute window for scheduling tolerance)
        $targetTime = $now->copy()->addHour();
        $windowStart = $targetTime->copy()->subMinutes(2);
        $windowEnd = $targetTime->copy()->addMinutes(2);
        
        // Check approved appointments (using original appointment_date and appointment_time)
        // Include appointments where action_taken is 'approve' (not rescheduled)
        $approvedAppointments = Appointment::where('action_taken', 'approve')
            ->whereNotNull('assigned_staff_id')
            ->whereNotNull('appointment_date')
            ->whereNotNull('appointment_time')
            ->whereNull('reminder_sent_at')
            ->with('assignedStaff')
            ->get();
        
        foreach ($approvedAppointments as $appointment) {
            if (!$appointment->assignedStaff || !$appointment->assignedStaff->email) {
                continue;
            }
            
            try {
                // Build appointment datetime from date and time
                // appointment_date is a Carbon date, appointment_time is a time string (HH:MM:SS or HH:MM)
                $dateStr = $appointment->appointment_date instanceof \Carbon\Carbon 
                    ? $appointment->appointment_date->format('Y-m-d')
                    : $appointment->appointment_date;
                
                $timeStr = $appointment->appointment_time;
                // Ensure time is in HH:MM:SS format
                if (preg_match('/^\d{2}:\d{2}$/', $timeStr)) {
                    $timeStr .= ':00';
                }
                
                $appointmentDateTime = Carbon::parse($dateStr . ' ' . $timeStr);
                
                // Only process future appointments
                if ($appointmentDateTime->isPast()) {
                    continue;
                }
                    
                // Check if appointment is exactly 1 hour from now (within 2-minute window)
                if ($appointmentDateTime->between($windowStart, $windowEnd)) {
                        // Send reminder email
                        try {
                            Mail::to($appointment->assignedStaff->email)->send(
                                new AppointmentReminderMail($appointment, $appointment->assignedStaff, false)
                            );
                            
                            // Mark reminder as sent
                            $appointment->update(['reminder_sent_at' => $now]);
                            $remindersSent++;
                            
                        $this->info("Reminder sent to {$appointment->assignedStaff->email} for appointment ID {$appointment->id} (scheduled for {$appointmentDateTime->format('Y-m-d H:i:s')})");
                        Log::info('Appointment reminder sent', [
                            'appointment_id' => $appointment->id,
                            'staff_email' => $appointment->assignedStaff->email,
                            'appointment_datetime' => $appointmentDateTime->format('Y-m-d H:i:s'),
                        ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to send appointment reminder', [
                                'appointment_id' => $appointment->id,
                                'staff_email' => $appointment->assignedStaff->email,
                                'error' => $e->getMessage(),
                            ]);
                            $this->error("Failed to send reminder for appointment ID {$appointment->id}: " . $e->getMessage());
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error processing appointment datetime', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
            }
        }
        
        // Check rescheduled appointments (using rescheduled_date and rescheduled_time)
        $rescheduledAppointments = Appointment::where('action_taken', 'reschedule')
            ->whereNotNull('assigned_staff_id')
            ->whereNotNull('rescheduled_date')
            ->whereNotNull('rescheduled_time')
            ->whereNull('rescheduled_reminder_sent_at')
            ->with('assignedStaff')
            ->get();
        
        foreach ($rescheduledAppointments as $appointment) {
            if (!$appointment->assignedStaff || !$appointment->assignedStaff->email) {
                continue;
            }
            
            try {
            // Build rescheduled datetime
                // rescheduled_date is a Carbon date, rescheduled_time is a time string (HH:MM:SS or HH:MM)
                $dateStr = $appointment->rescheduled_date instanceof \Carbon\Carbon 
                    ? $appointment->rescheduled_date->format('Y-m-d')
                    : $appointment->rescheduled_date;
                
                $timeStr = $appointment->rescheduled_time;
                // Ensure time is in HH:MM:SS format
                if (preg_match('/^\d{2}:\d{2}$/', $timeStr)) {
                    $timeStr .= ':00';
                }
                
                $rescheduledDateTime = Carbon::parse($dateStr . ' ' . $timeStr);
                
                // Only process future appointments
                if ($rescheduledDateTime->isPast()) {
                    continue;
                }
                
                // Check if rescheduled appointment is exactly 1 hour from now (within 2-minute window)
                if ($rescheduledDateTime->between($windowStart, $windowEnd)) {
                    // Send reminder email
                    try {
                        Mail::to($appointment->assignedStaff->email)->send(
                            new AppointmentReminderMail($appointment, $appointment->assignedStaff, true)
                        );
                        
                        // Mark reminder as sent
                        $appointment->update(['rescheduled_reminder_sent_at' => $now]);
                        $remindersSent++;
                        
                        $this->info("Rescheduled reminder sent to {$appointment->assignedStaff->email} for appointment ID {$appointment->id} (rescheduled for {$rescheduledDateTime->format('Y-m-d H:i:s')})");
                        Log::info('Rescheduled appointment reminder sent', [
                            'appointment_id' => $appointment->id,
                            'staff_email' => $appointment->assignedStaff->email,
                            'rescheduled_datetime' => $rescheduledDateTime->format('Y-m-d H:i:s'),
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send rescheduled appointment reminder', [
                            'appointment_id' => $appointment->id,
                            'staff_email' => $appointment->assignedStaff->email,
                            'error' => $e->getMessage(),
                        ]);
                        $this->error("Failed to send rescheduled reminder for appointment ID {$appointment->id}: " . $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing rescheduled appointment datetime', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        $this->info("Completed. Sent {$remindersSent} reminder(s).");
        
        return Command::SUCCESS;
    }
}
