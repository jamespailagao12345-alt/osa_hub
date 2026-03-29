<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'addressable_type',
        'addressable_id',
        'type',
        'street',
        'barangay',
        'city_municipality',
        'province',
        'zip_code',
        'complete_address',
    ];

    /**
     * Get the parent addressable model (User, Student, etc.).
     */
    public function addressable()
    {
        return $this->morphTo();
    }
}
