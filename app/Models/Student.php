<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'student_information';

    // Only include columns that actually exist in student_information table
    // Note: first_name, last_name, email, contact_number, etc. are in users table, not student_information
    protected $fillable = [
        'user_id',
        'student_id',
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

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function course()
    {
        return $this->belongsTo(\App\Models\Course::class, 'course_id');
    }

    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class, 'organization_id');
    }

    public function scholarship()
    {
        return $this->belongsTo(\App\Models\Scholarship::class, 'scholarship_id');
    }
}
