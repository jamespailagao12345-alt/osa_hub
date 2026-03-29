<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\CachesReferenceData;

class Scholarship extends Model
{
    use HasFactory, CachesReferenceData;

    protected $fillable = ['name'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}