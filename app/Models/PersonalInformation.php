<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'age',
        'civil_status',
        'maiden_name',
        'place_of_birth',
        'nationality',
        'religion',
        'sport',
        'arts',
        'technical',
        'is_indigenous_group_member',
        'indigenous_group_specify',
        'is_pwd',
        'pwd_id_image',
        'is_government_member',
        'government_level',
        'government_role_position',
        'living_arrangement',
        'living_arrangement_others_specify',
        'is_single_parent',
        'fraternity_sorority_name',
        'fraternity_sorority_position',
        'has_criminal_record',
    ];

    protected $casts = [
        'is_indigenous_group_member' => 'boolean',
        'is_pwd' => 'boolean',
        'is_single_parent' => 'boolean',
        'has_criminal_record' => 'boolean',
    ];

    /**
     * Get the user that owns the personal information.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the nationality for this personal information.
     */
    public function nationality()
    {
        return $this->belongsTo(Nationality::class, 'nationality_id');
    }

    /**
     * Get the PWD information for this user.
     */
    public function pwdInformation()
    {
        return $this->hasOne(PwdInformation::class, 'user_id', 'user_id');
    }

    /**
     * Get the indigenous member information for this user.
     */
    public function indigenousMember()
    {
        return $this->hasOne(IndigenousMember::class, 'user_id', 'user_id');
    }

    /**
     * Get the government affiliation for this user.
     */
    public function governmentAffiliation()
    {
        return $this->hasOne(GovernmentAffiliation::class, 'user_id', 'user_id');
    }

    /**
     * Get the fraternity member information for this user.
     */
    public function fraternityMember()
    {
        return $this->hasOne(FraternityMember::class, 'user_id', 'user_id');
    }
}
