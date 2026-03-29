<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $table = 'staff_information';

    protected $fillable = [
        'user_id',
        'department_id',
        'designation',
        'contact_number',
        'image',
        'birth_date',
        'gender',
        'age',
        'middle_name',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
