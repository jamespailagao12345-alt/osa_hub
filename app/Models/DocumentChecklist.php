<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'form_137_presented',
        'tor_presented',
        'good_moral_cert_presented',
        'birth_cert_presented',
        'marriage_cert_presented',
        'personal_data_sheet_image',
    ];

    protected $casts = [
        'form_137_presented' => 'boolean',
        'tor_presented' => 'boolean',
        'good_moral_cert_presented' => 'boolean',
        'birth_cert_presented' => 'boolean',
        'marriage_cert_presented' => 'boolean',
    ];

    /**
     * Get the user that owns the document checklist.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
