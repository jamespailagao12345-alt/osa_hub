<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FraternityMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fraternity_sorority_name',
        'fraternity_sorority_position',
        'type',
        'membership_start_date',
        'membership_end_date',
        'notes',
    ];

    protected $casts = [
        'membership_start_date' => 'date',
        'membership_end_date' => 'date',
    ];

    /**
     * Get the user that owns the fraternity membership.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
