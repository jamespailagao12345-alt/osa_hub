<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'relation',
        'name',
        'contact_number',
        'occupation',
        'workplace',
        'monthly_income',
        'relationship',
    ];

    /**
     * Get the user that owns the family member.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
