<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'year_level',
        'student_type1',
        'student_type2',
        'student_type',
        'school_year',
        'semester',
        'academic_year',
        'scholarship_id',
        'is_active_scholar',
        'scholarship_grant_name',
    ];

    /**
     * Get the user that owns the student information.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scholarship associated with the student.
     */
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
}
