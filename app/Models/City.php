<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'province_id',
        'name',
        'zip_code',
    ];

    /**
     * Get the province that owns this city
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get all barangays for this city
     */
    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class);
    }
}
