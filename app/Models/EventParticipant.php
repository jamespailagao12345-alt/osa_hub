<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'user_id', 'qr_scanned', 'scanned_at', 'scanned_by', 'attendance_status'
    ];

    protected $casts = [
        'qr_scanned' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // student
    }

    public function scanner()
    {
        return $this->belongsTo(User::class, 'scanned_by'); // staff/assistant
    }
}