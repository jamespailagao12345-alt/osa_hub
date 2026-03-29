<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'event_id',
        'scan_time',
        'status',
        'excuse_letter',
    ];

    protected $casts = [
        'scan_time' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

