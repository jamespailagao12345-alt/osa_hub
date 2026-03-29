<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CachesReferenceData;

class Nationality extends Model
{
    use HasFactory, CachesReferenceData;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all users with this nationality.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'nationality_id');
    }

    /**
     * Get all personal information records with this nationality.
     */
    public function personalInformation()
    {
        return $this->hasMany(PersonalInformation::class, 'nationality_id');
    }
}
