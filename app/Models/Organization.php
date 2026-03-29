<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\CachesReferenceData;

class Organization extends Model
{
    use HasFactory, CachesReferenceData;

    protected $fillable = ['name', 'is_special', 'department_id', 'official_email', 'acronym', 'mailing_address', 'date_established'];

    protected $casts = [
        'date_established' => 'date',
        'is_special' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get users who belong to this organization via the pivot table (additional organizations).
     * Allows multiple memberships with positions.
     */
    public function otherUsers()
    {
        return $this->belongsToMany(User::class, 'organization_user', 'organization_id', 'user_id')
            ->withPivot('position')
            ->withTimestamps();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get staff members who belong to this organization via the pivot table.
     */
    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'organization_staff', 'organization_id', 'staff_id');
    }

    /**
     * Get staff members who have this as their primary organization.
     */
    public function primaryStaff()
    {
        return $this->hasMany(Staff::class, 'organization_id');
    }
}