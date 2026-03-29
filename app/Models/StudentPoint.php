<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'feedback_id',
        'points',
        'notes',
        'awarded_at',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function feedback()
    {
        return $this->belongsTo(EventFeedback::class, 'feedback_id');
    }
}
