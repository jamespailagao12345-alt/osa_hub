<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\CachesReferenceData;

class Section extends Model
{
    use HasFactory, CachesReferenceData;

    protected $fillable = ['course_id', 'name'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Note: Users relationship not yet implemented
    // When section_id is added to users table, uncomment and configure:
    // public function users()
    // {
    //     return $this->hasMany(User::class, 'section_id');
    // }
}
