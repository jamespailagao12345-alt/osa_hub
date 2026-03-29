<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EducationalBackground extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'level',
        'school_name',
        'address',
        'year_graduated',
        'year_completed',
        'course',
        'track_strand',
        'lrn',
        'honors_awards',
    ];

    /**
     * Get the user that owns the educational background.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
