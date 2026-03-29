<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\CachesReferenceData;

class Designation extends Model
{
    use CachesReferenceData;

    protected $fillable = ['name', 'features'];

    protected $casts = [
        'features' => 'array',
    ];
}
