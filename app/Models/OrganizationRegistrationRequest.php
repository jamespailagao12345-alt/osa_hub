<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationRegistrationRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'student_id',
        'organization_id',
        'status',
        'details',
        'position',
    ];
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }
}
