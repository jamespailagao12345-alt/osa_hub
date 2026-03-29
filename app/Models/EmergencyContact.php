<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'contact_number',
        'relation',
    ];

    /**
     * Get the user that owns the emergency contact.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
