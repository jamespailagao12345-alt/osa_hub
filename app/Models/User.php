<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\CachesReferenceData;

class User extends Authenticatable
{
    use HasFactory, Notifiable, CachesReferenceData;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'ext_name',
        'email',
        'gender',
        'birth_date',
        'age',
        'civil_status',
        'maiden_name',
        'place_of_birth',
        'nationality',
        'religion',
        'department_id',
        'course_id',
        'organization_id',
        'year_level',
        'student_type1',
        'student_type2',
        'student_type',
        'school_year',
        'semester',
        'academic_year',
        'scholarship_id',
        'contact_number',
        'tel_no',
        'complete_home_address',
        'street',
        'barangay',
        'city_municipality',
        'province',
        'zip_code',
        'emergency_contact_name',
        'emergency_contact_number',
        'emergency_relation',
        'parent_spouse_guardian',
        'parent_spouse_guardian_address',
        'spouse_name',
        'spouse_contact_no',
        'sport',
        'arts',
        'technical',
        'elementary_school',
        'elementary_address',
        'elementary_year_graduated',
        'junior_high_school_name',
        'junior_high_school_year_completed',
        'junior_high_school_address',
        'junior_high_school_honors_awards',
        'senior_high_school_name',
        'senior_high_school_year_graduated',
        'senior_high_school_track_strand',
        'senior_high_school_lrn',
        'senior_high_school_address',
        'senior_high_school_honors_awards',
        'high_school',
        'high_school_address',
        'high_school_year_graduated',
        'college_name',
        'college_address',
        'college_course',
        'college_year',
        'last_school_attended',
        'last_school_course',
        'last_school_address',
        'last_school_year_attended',
        'father_name',
        'father_contact_number',
        'father_occupation',
        'father_workplace',
        'father_monthly_income',
        'mother_name',
        'mother_contact_number',
        'mother_occupation',
        'mother_workplace',
        'mother_monthly_income',
        'guardian_name',
        'guardian_relationship',
        'guardian_contact_number',
        'guardian_occupation',
        'guardian_workplace',
        'guardian_monthly_income',
        'is_active_scholar',
        'scholarship_grant_name',
        'is_indigenous_group_member',
        'indigenous_group_specify',
        'is_pwd',
        'pwd_id_image',
        'is_government_member',
        'government_level',
        'government_role_position',
        'living_arrangement',
        'living_arrangement_others_specify',
        'is_single_parent',
        'fraternity_sorority_name',
        'fraternity_sorority_position',
        'has_criminal_record',
        'form_137_presented',
        'verification_email_count',
        'tor_presented',
        'good_moral_cert_presented',
        'birth_cert_presented',
        'marriage_cert_presented',
        'role',
        'position',
        'password',
        'email_verified_at',
        'image',
        'service_order',
        'length_of_service',
        'contract_end_at',
        'suspended',
        'suspension_reason',
        'last_imported_worksheet',
        'about_me',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'role' => 'integer',
        'password' => 'hashed',
        'contract_end_at' => 'date',
        'suspended' => 'boolean',
    ];

    /**
     * Get the department that owns the user.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(Staff::class, 'user_id');
    }

    /**
     * Get the course that owns the user.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the organization that owns the user.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Additional organizations the user belongs to (many-to-many).
     * Allows multiple memberships with positions.
     */
    public function otherOrganizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('position')
            ->withTimestamps();
    }

    /**
     * Get the scholarship that owns the user.
     */
    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }
        /**
         * Get student leaders under this staff (if staff).
         */
        public function assistants()
        {
            return $this->hasMany(User::class, 'supervisor_id');
        }

        /**
         * Get the supervisor (if assistant).
         */
        public function supervisor()
        {
            return $this->belongsTo(User::class, 'supervisor_id');
        }

        public function assistantAssignments()
        {
            return $this->hasMany(AssistantAssignment::class);
        }

        /**
         * Get leadership background entries for this assistant.
         */
        public function leadershipBackgrounds()
        {
            return $this->hasMany(AssistantLeadershipBackground::class)->orderBy('order');
        }

        public function hasAssistantAccess(): bool
        {
            // Role 3 is assistant; role 1 (student) may have assistant assignments
            if (($this->role ?? null) === 3) return true;
            if (($this->role ?? null) === 1) {
                return $this->assistantAssignments()->where('is_active', true)->count() > 0;
            }
            return false;
        }

        /**
         * Get the student record associated with this user (if role=1).
         */
        public function student()
        {
            return $this->hasOne(Student::class, 'user_id', 'id');
        }

        /**
         * Get event feedback submitted by this user.
         */
        public function eventFeedback()
        {
            return $this->hasMany(EventFeedback::class);
        }

        /**
         * Get points earned by this user.
         */
        public function studentPoints()
        {
            return $this->hasMany(StudentPoint::class);
        }

        /**
         * Get total points earned by this user.
         */
        public function getTotalPointsAttribute()
        {
            return $this->studentPoints()->sum('points');
        }

        /**
         * Get all addresses for this user.
         */
        public function addresses()
        {
            return $this->morphMany(Address::class, 'addressable');
        }

        /**
         * Get the home address for this user.
         */
        public function homeAddress()
        {
            return $this->morphOne(Address::class, 'addressable')->where('type', 'home');
        }

        /**
         * Get emergency contacts for this user.
         */
        public function emergencyContacts()
        {
            return $this->hasMany(EmergencyContact::class);
        }

        /**
         * Get family members for this user.
         */
        public function familyMembers()
        {
            return $this->hasMany(FamilyMember::class);
        }

        /**
         * Get a specific family member by relation.
         */
        public function familyMember($relation)
        {
            return $this->hasOne(FamilyMember::class)->where('relation', $relation);
        }

        /**
         * Get educational backgrounds for this user.
         */
        public function educationalBackgrounds()
        {
            return $this->hasMany(EducationalBackground::class);
        }

        /**
         * Get educational background by level.
         */
        public function educationalBackground($level)
        {
            return $this->hasOne(EducationalBackground::class)->where('level', $level);
        }

        /**
         * Get student information for this user.
         */
        public function studentInformation()
        {
            return $this->hasOne(StudentInformation::class);
        }

        /**
         * Get personal information for this user.
         */
        public function personalInformation()
        {
            return $this->hasOne(PersonalInformation::class);
        }

        /**
         * Get document checklist for this user.
         */
        public function documentChecklist()
        {
            return $this->hasOne(DocumentChecklist::class);
        }

        /**
         * Get PWD information for this user.
         */
        public function pwdInformation()
        {
            return $this->hasOne(PwdInformation::class);
        }

        /**
         * Get indigenous member information for this user.
         */
        public function indigenousMember()
        {
            return $this->hasOne(IndigenousMember::class);
        }

        /**
         * Get government affiliation for this user.
         */
        public function governmentAffiliation()
        {
            return $this->hasOne(GovernmentAffiliation::class);
        }

        /**
         * Get fraternity member information for this user.
         */
        public function fraternityMember()
        {
            return $this->hasOne(FraternityMember::class);
        }

        /**
         * Get nationality for this user (if stored in personal_information).
         */
        public function nationality()
        {
            return $this->hasOneThrough(
                Nationality::class,
                PersonalInformation::class,
                'user_id', // Foreign key on personal_information table
                'id', // Foreign key on nationalities table
                'id', // Local key on users table
                'nationality_id' // Local key on personal_information table
            );
        }
}