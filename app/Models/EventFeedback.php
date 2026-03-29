<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'feedback_text',
        'rating',
        'points_awarded',
        'submitted_at',
    ];

    protected $casts = [
        'points_awarded' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function studentPoint()
    {
        return $this->hasOne(StudentPoint::class, 'feedback_id');
    }
}
