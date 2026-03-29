<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;

    public function requirements()
    {
        return $this->hasMany(EventRequirement::class);
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function files()
    {
        return $this->hasMany(EventFile::class);
    }
    protected $fillable = [
        'name', 'description', 'event_date', 'end_date', 'location', 'organization_id', 'coordinator_name', 'status', 'start_time', 'end_time', 'qr_code_path', 'created_by', 'decline_reason', 'required_student_participation',
        'monitoring_started', 'monitoring_started_at', 'attended_threshold_minutes', 'late_threshold_minutes', 'absent_threshold_minutes', 'points'
    ];

    protected $casts = [
        'event_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'required_student_participation' => 'boolean',
        'monitoring_started' => 'boolean',
        'monitoring_started_at' => 'datetime',
    ];

    public function attendances() {
        return $this->hasMany(Attendance::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function feedback()
    {
        return $this->hasMany(EventFeedback::class);
    }

    public function studentPoints()
    {
        return $this->hasMany(StudentPoint::class);
    }
}