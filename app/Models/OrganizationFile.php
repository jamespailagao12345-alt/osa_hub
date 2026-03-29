<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_category',
        'description',
        'file_size',
        'mime_type',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the organization that owns this file.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who uploaded this file.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file size in human readable format.
     */
    public function getHumanReadableSizeAttribute()
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

