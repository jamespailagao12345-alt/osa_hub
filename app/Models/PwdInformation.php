<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PwdInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_pwd',
        'pwd_id_image',
        'pwd_id_number',
        'disability_type',
        'notes',
    ];

    protected $casts = [
        'is_pwd' => 'boolean',
    ];

    /**
     * Get the user that owns the PWD information.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
