<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'full_name', 'email', 'contact_number',
        'appointment_date', 'appointment_time', 'concern', 'message',
        'assigned_staff_id', 'status', 'session', 'action_taken', 'action_reason',
        'rescheduled_date', 'rescheduled_time', 'reason_for_counseling', 'category',
        'reminder_sent_at', 'rescheduled_reminder_sent_at', 'remarks'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        // 'appointment_time' => 'time', // Remove unsupported cast
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function files()
    {
        return $this->hasMany(AppointmentFile::class);
    }
}