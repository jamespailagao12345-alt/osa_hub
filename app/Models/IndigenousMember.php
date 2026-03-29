<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndigenousMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_indigenous_group_member',
        'indigenous_group_specify',
        'tribal_affiliation',
        'notes',
    ];

    protected $casts = [
        'is_indigenous_group_member' => 'boolean',
    ];

    /**
     * Get the user that owns the indigenous member information.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
