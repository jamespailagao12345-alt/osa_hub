<?php
namespace App\Http\Controllers\Admin;

use App\Models\Student;
use App\Models\Address;
use App\Models\EmergencyContact;
use App\Models\FamilyMember;
use App\Models\EducationalBackground;
use App\Models\StudentInformation;
use App\Models\PersonalInformation;
use App\Models\DocumentChecklist;
use App\Models\Nationality;
use App\Models\PwdInformation;
use App\Models\IndigenousMember;
use App\Models\GovernmentAffiliation;
use App\Models\FraternityMember;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Helpers\EmailHelper;
use App\Mail\AccountCredentialsMail;

class StudentController extends Controller
{
    public function index()
    {
        // Fetch students with all relationships, including user data
        $students = \App\Models\Student::with(['user', 'department', 'course', 'organization', 'scholarship'])
            ->get()
            ->map(function($student) {
                // Merge user data into student for easier access
                if ($student->user) {
                    $student->user_id_display = $student->user->user_id;
                    // Sync any missing data from user to student for display
                    if (!$student->email && $student->user->email) {
                        $student->email = $student->user->email;
                    }
                    if (!$student->contact_number && $student->user->contact_number) {
                        $student->contact_number = $student->user->contact_number;
                    }
                }
                return $student;
            })
            ->sortBy(function($student) {
                // Sort alphabetically by last name, then first name (case-insensitive)
                $lastName = strtolower(($student->last_name ?? optional($student->user)->last_name) ?? '');
                $firstName = strtolower(($student->first_name ?? optional($student->user)->first_name) ?? '');
                return $lastName . ' ' . $firstName;
            })
            ->values();
        // Use cached reference data
        $departments = \App\Services\CacheService::getDepartments();
        $courses = \App\Services\CacheService::getCourses();
        $organizations = \App\Services\CacheService::getOrganizations();
        $scholarships = \App\Services\CacheService::getScholarships();
        $nationalities = \App\Services\CacheService::getNationalities()->where('is_active', true);
        return view('admin.staff.dashboard.AdmissionServicesOfficer.student-management', compact('students', 'departments', 'courses', 'organizations', 'scholarships', 'nationalities'));
    }

    /**
     * Save data to normalized tables (for create)
     */
    private function saveNormalizedData($user, $validated, $pwdIdImagePath = null)
    {
        $this->updateNormalizedData($user, $validated, $pwdIdImagePath, true);
    }

    /**
     * Update data in normalized tables (for update)
     */
    private function updateNormalizedData($user, $validated, $pwdIdImagePath = null, $isCreate = false)
    {
        // 1. Update Address (delete old, create new)
        if (!$isCreate) {
            $user->addresses()->where('type', 'home')->delete();
        }
        if ($validated['complete_home_address'] ?? $validated['street'] ?? $validated['barangay'] ?? null) {
            Address::create([
                'addressable_type' => \App\Models\User::class,
                'addressable_id' => $user->id,
                'type' => 'home',
                'street' => $validated['street'] ?? null,
                'barangay' => $validated['barangay'] ?? null,
                'city_municipality' => $validated['city_municipality'] ?? null,
                'province' => $validated['province'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
                'complete_address' => $validated['complete_home_address'] ?? null,
            ]);
        }

        // 2. Update Emergency Contact
        if (!$isCreate) {
            $user->emergencyContacts()->delete();
        }
        if ($validated['emergency_contact_name'] ?? null) {
            EmergencyContact::create([
                'user_id' => $user->id,
                'name' => $validated['emergency_contact_name'],
                'contact_number' => $validated['emergency_contact_number'] ?? '',
                'relation' => $validated['emergency_relation'] ?? null,
            ]);
        }

        // 3. Update Family Members (delete old, create new)
        if (!$isCreate) {
            $user->familyMembers()->delete();
        }
        if ($validated['father_name'] ?? null) {
            FamilyMember::create([
                'user_id' => $user->id,
                'relation' => 'father',
                'name' => $validated['father_name'],
                'contact_number' => $validated['father_contact_number'] ?? null,
                'occupation' => $validated['father_occupation'] ?? null,
                'workplace' => $validated['father_workplace'] ?? null,
                'monthly_income' => $validated['father_monthly_income'] ?? null,
            ]);
        }

        if ($validated['mother_name'] ?? null) {
            FamilyMember::create([
                'user_id' => $user->id,
                'relation' => 'mother',
                'name' => $validated['mother_name'],
                'contact_number' => $validated['mother_contact_number'] ?? null,
                'occupation' => $validated['mother_occupation'] ?? null,
                'workplace' => $validated['mother_workplace'] ?? null,
                'monthly_income' => $validated['mother_monthly_income'] ?? null,
            ]);
        }

        if ($validated['guardian_name'] ?? null) {
            FamilyMember::create([
                'user_id' => $user->id,
                'relation' => 'guardian',
                'name' => $validated['guardian_name'],
                'contact_number' => $validated['guardian_contact_number'] ?? null,
                'occupation' => $validated['guardian_occupation'] ?? null,
                'workplace' => $validated['guardian_workplace'] ?? null,
                'monthly_income' => $validated['guardian_monthly_income'] ?? null,
                'relationship' => $validated['guardian_relationship'] ?? null,
            ]);
        }

        if ($validated['spouse_name'] ?? null) {
            FamilyMember::create([
                'user_id' => $user->id,
                'relation' => 'spouse',
                'name' => $validated['spouse_name'],
                'contact_number' => $validated['spouse_contact_no'] ?? null,
            ]);
        }

        // 4. Update Educational Backgrounds (delete old, create new)
        if (!$isCreate) {
            $user->educationalBackgrounds()->delete();
        }
        if ($validated['elementary_school'] ?? null) {
            EducationalBackground::create([
                'user_id' => $user->id,
                'level' => 'elementary',
                'school_name' => $validated['elementary_school'],
                'address' => $validated['elementary_address'] ?? null,
                'year_graduated' => $validated['elementary_year_graduated'] ?? null,
            ]);
        }

        if ($validated['junior_high_school_name'] ?? null) {
            EducationalBackground::create([
                'user_id' => $user->id,
                'level' => 'junior_high',
                'school_name' => $validated['junior_high_school_name'],
                'address' => $validated['junior_high_school_address'] ?? null,
                'year_completed' => $validated['junior_high_school_year_completed'] ?? null,
                'honors_awards' => $validated['junior_high_school_honors_awards'] ?? null,
            ]);
        }

        if ($validated['senior_high_school_name'] ?? null) {
            EducationalBackground::create([
                'user_id' => $user->id,
                'level' => 'senior_high',
                'school_name' => $validated['senior_high_school_name'],
                'address' => $validated['senior_high_school_address'] ?? null,
                'year_graduated' => $validated['senior_high_school_year_graduated'] ?? null,
                'track_strand' => $validated['senior_high_school_track_strand'] ?? null,
                'lrn' => $validated['senior_high_school_lrn'] ?? null,
                'honors_awards' => $validated['senior_high_school_honors_awards'] ?? null,
            ]);
        } elseif ($validated['high_school'] ?? null) {
            EducationalBackground::create([
                'user_id' => $user->id,
                'level' => 'senior_high',
                'school_name' => $validated['high_school'],
                'address' => $validated['high_school_address'] ?? null,
                'year_graduated' => $validated['high_school_year_graduated'] ?? null,
            ]);
        }

        if ($validated['college_name'] ?? null) {
            EducationalBackground::create([
                'user_id' => $user->id,
                'level' => 'college',
                'school_name' => $validated['college_name'],
                'address' => $validated['college_address'] ?? null,
                'course' => $validated['college_course'] ?? null,
                'year_graduated' => $validated['college_year'] ?? null,
            ]);
        }

        if ($validated['last_school_attended'] ?? null) {
            EducationalBackground::create([
                'user_id' => $user->id,
                'level' => 'last_school',
                'school_name' => $validated['last_school_attended'],
                'address' => $validated['last_school_address'] ?? null,
                'course' => $validated['last_school_course'] ?? null,
                'year_graduated' => $validated['last_school_year_attended'] ?? null,
            ]);
        }

        // 5. Update Student Information
        if ($validated['year_level'] ?? $validated['student_type1'] ?? $validated['student_type2'] ?? null) {
            if ($isCreate) {
                StudentInformation::create([
                    'user_id' => $user->id,
                    'year_level' => $validated['year_level'] ?? null,
                    'student_type1' => $validated['student_type1'] ?? null,
                    'student_type2' => $validated['student_type2'] ?? null,
                    'student_type' => $validated['student_type'] ?? null,
                    'school_year' => $validated['school_year'] ?? null,
                    'semester' => $validated['semester'] ?? null,
                    'academic_year' => $validated['academic_year'] ?? null,
                    'scholarship_id' => $validated['scholarship_id'] ?? null,
                    'is_active_scholar' => $validated['is_active_scholar'] ?? false,
                    'scholarship_grant_name' => $validated['scholarship_grant_name'] ?? null,
                ]);
            } else {
                $user->studentInformation()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'year_level' => $validated['year_level'] ?? null,
                        'student_type1' => $validated['student_type1'] ?? null,
                        'student_type2' => $validated['student_type2'] ?? null,
                        'student_type' => $validated['student_type'] ?? null,
                        'school_year' => $validated['school_year'] ?? null,
                        'semester' => $validated['semester'] ?? null,
                        'academic_year' => $validated['academic_year'] ?? null,
                        'scholarship_id' => $validated['scholarship_id'] ?? null,
                        'is_active_scholar' => $validated['is_active_scholar'] ?? false,
                        'scholarship_grant_name' => $validated['scholarship_grant_name'] ?? null,
                    ]
                );
            }
        }
        
        // 6. Update Personal Information (basic fields only)
        $personalInfoData = [
            'age' => $validated['age'] ?? null,
            'civil_status' => $validated['civil_status'] ?? null,
            'maiden_name' => $validated['maiden_name'] ?? null,
            'place_of_birth' => $validated['place_of_birth'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'sport' => $validated['sport'] ?? null,
            'arts' => $validated['arts'] ?? null,
            'technical' => $validated['technical'] ?? null,
            'living_arrangement' => $validated['living_arrangement'] ?? null,
            'living_arrangement_others_specify' => $validated['living_arrangement_others_specify'] ?? null,
            'is_single_parent' => $validated['is_single_parent'] ?? false,
            'has_criminal_record' => $validated['has_criminal_record'] ?? false,
        ];

        // Handle nationality - use nationality_id if provided, otherwise find or create by name
        if (!empty($validated['nationality_id'] ?? null)) {
            $personalInfoData['nationality_id'] = $validated['nationality_id'];
        } elseif (!empty($validated['nationality'] ?? null)) {
            $nationalityName = trim($validated['nationality']);
            $nationality = Nationality::firstOrCreate(
                ['name' => $nationalityName],
                ['code' => null, 'is_active' => true]
            );
            $personalInfoData['nationality_id'] = $nationality->id;
        }

        $user->personalInformation()->updateOrCreate(
            ['user_id' => $user->id],
            $personalInfoData
        );

        // 6a. Save/Update PWD Information
        $pwdData = [
            'is_pwd' => $validated['is_pwd'] ?? false,
            'disability_type' => $validated['disability_type'] ?? null,
        ];
        if ($pwdIdImagePath) {
            $pwdData['pwd_id_image'] = $pwdIdImagePath;
        }
        PwdInformation::updateOrCreate(
            ['user_id' => $user->id],
            $pwdData
        );

        // 6b. Save/Update Indigenous Member Information
        if (($validated['is_indigenous_group_member'] ?? false) || !empty($validated['indigenous_group_specify'] ?? null)) {
            IndigenousMember::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_indigenous_group_member' => $validated['is_indigenous_group_member'] ?? false,
                    'indigenous_group_specify' => $validated['indigenous_group_specify'] ?? null,
                ]
            );
        }

        // 6c. Save/Update Government Affiliation
        if (!empty($validated['is_government_member'] ?? null) || !empty($validated['government_level'] ?? null)) {
            GovernmentAffiliation::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_government_member' => $validated['is_government_member'] ?? null,
                    'government_level' => $validated['government_level'] ?? null,
                    'government_role_position' => $validated['government_role_position'] ?? null,
                ]
            );
        }

        // 6d. Save/Update Fraternity Member Information
        if (!empty($validated['fraternity_sorority_name'] ?? null) || !empty($validated['fraternity_sorority_position'] ?? null)) {
            // Determine type based on name
            $type = null;
            if (!empty($validated['fraternity_sorority_name'])) {
                $nameLower = strtolower($validated['fraternity_sorority_name']);
                if (strpos($nameLower, 'sorority') !== false) {
                    $type = 'sorority';
                } elseif (strpos($nameLower, 'fraternity') !== false) {
                    $type = 'fraternity';
                }
            }

            FraternityMember::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'fraternity_sorority_name' => $validated['fraternity_sorority_name'] ?? null,
                    'fraternity_sorority_position' => $validated['fraternity_sorority_position'] ?? null,
                    'type' => $type,
                ]
            );
        }

        // 7. Save/Update Document Checklist
        if ($isCreate) {
            DocumentChecklist::create([
                'user_id' => $user->id,
                'form_137_presented' => $validated['form_137_presented'] ?? false,
                'tor_presented' => $validated['tor_presented'] ?? false,
                'good_moral_cert_presented' => $validated['good_moral_cert_presented'] ?? false,
                'birth_cert_presented' => $validated['birth_cert_presented'] ?? false,
                'marriage_cert_presented' => $validated['marriage_cert_presented'] ?? false,
            ]);
        } else {
            $user->documentChecklist()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'form_137_presented' => $validated['form_137_presented'] ?? false,
                    'tor_presented' => $validated['tor_presented'] ?? false,
                    'good_moral_cert_presented' => $validated['good_moral_cert_presented'] ?? false,
                    'birth_cert_presented' => $validated['birth_cert_presented'] ?? false,
                    'marriage_cert_presented' => $validated['marriage_cert_presented'] ?? false,
                ]
            );
        }
    }

    public function store(Request $request)
    {
        // Check for duplicates before validation
        $existingUserByUserId = \App\Models\User::where('user_id', $request->user_id)->first();
        $existingUserByEmail = \App\Models\User::where('email', $request->email)->first();
        
        if ($existingUserByUserId || $existingUserByEmail) {
            // Find the student associated with the duplicate user
            $duplicateUser = $existingUserByUserId ?? $existingUserByEmail;
            $duplicateStudent = Student::where('user_id', $duplicateUser->id)->with(['department', 'course', 'organization', 'scholarship'])->first();
            
            if ($duplicateStudent) {
                return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')
                    ->withInput()
                    ->with('duplicate_student_id', $duplicateStudent->id)
                    ->with('duplicate_message', 'A student with this Student ID or Email has already been added. Do you want to see the student\'s details?');
            } else {
                return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')
                    ->withInput()
                    ->with('error', 'A user with this Student ID or Email already exists, but no associated student record was found.');
            }
        }
        
        $validated = $request->validate([
            // A. NAME
            'user_id' => ['required', 'unique:users,user_id', new \App\Rules\UserIdByRole(1)],
            'first_name' => 'required',
            'last_name' => 'required',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'ext_name' => 'nullable|string|max:50',
            // B. HOME ADDRESS
            'street' => 'nullable|string',
            'barangay' => 'nullable|string',
            'city_municipality' => 'nullable|string',
            'province' => 'nullable|string',
            'zip_code' => 'nullable|string|max:20',
            // C. PERSONAL DETAILS
            'age' => 'required|integer|min:1|max:100',
            'birth_date' => 'required|date',
            'place_of_birth' => 'required|string',
            'gender' => 'required|in:male,female,other',
            'civil_status' => 'required|in:single,married,divorced,widowed',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'nationality' => 'nullable|string|max:255',
            // D. Other
            'religion' => 'nullable|string',
            'contact_number' => 'required|string',
            'tel_no' => 'nullable|string|max:50',
            'email' => 'required|email|unique:users',
            'spouse_name' => 'nullable|string',
            'spouse_contact_no' => 'nullable|string|max:50',
            // E. SPECIAL SKILLS AND TALENTS
            'sport' => 'nullable|string',
            'arts' => 'nullable|string',
            'technical' => 'nullable|string',
            // F. EDUCATION BACKGROUND
            'junior_high_school_name' => 'nullable|string',
            'junior_high_school_year_completed' => 'nullable|string|max:20',
            'junior_high_school_address' => 'nullable|string',
            'junior_high_school_honors_awards' => 'nullable|string',
            'senior_high_school_name' => 'nullable|string',
            'senior_high_school_year_graduated' => 'nullable|string|max:20',
            'senior_high_school_track_strand' => 'nullable|string',
            'senior_high_school_lrn' => 'nullable|string|max:50',
            'senior_high_school_address' => 'nullable|string',
            'senior_high_school_honors_awards' => 'nullable|string',
            'last_school_attended' => 'nullable|string',
            'last_school_course' => 'nullable|string',
            'last_school_address' => 'nullable|string',
            'last_school_year_attended' => 'nullable|string|max:20',
            // G. FAMILY BACKGROUND
            'father_name' => 'nullable|string',
            'father_contact_number' => 'nullable|string|max:50',
            'father_occupation' => 'nullable|string',
            'father_workplace' => 'nullable|string',
            'father_monthly_income' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'mother_contact_number' => 'nullable|string|max:50',
            'mother_occupation' => 'nullable|string',
            'mother_workplace' => 'nullable|string',
            'mother_monthly_income' => 'nullable|string',
            'guardian_name' => 'nullable|string',
            'guardian_relationship' => 'nullable|string',
            'guardian_contact_number' => 'nullable|string|max:50',
            'guardian_occupation' => 'nullable|string',
            'guardian_workplace' => 'nullable|string',
            'guardian_monthly_income' => 'nullable|string',
            // H. OTHER INFORMATION
            'is_active_scholar' => 'nullable|boolean',
            'scholarship_grant_name' => 'nullable|string',
            'is_indigenous_group_member' => 'nullable|boolean',
            'indigenous_group_specify' => 'nullable|string',
            'is_pwd' => 'nullable|boolean',
            'disability_type' => 'nullable|string|max:255',
            'pwd_id_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:102400', // 100MB max
            'is_government_member' => 'nullable|in:no,yes',
            'government_level' => 'nullable|in:barangay,municipal_city,provincial',
            'government_role_position' => 'nullable|string',
            'living_arrangement' => 'nullable|in:home,boarding_house,relatives,working_student,others',
            'living_arrangement_others_specify' => 'nullable|string',
            'is_single_parent' => 'nullable|boolean',
            'fraternity_sorority_name' => 'nullable|string',
            'fraternity_sorority_position' => 'nullable|string',
            'has_criminal_record' => 'nullable|boolean',
            // Legacy fields
            'complete_home_address' => 'nullable|string',
            'maiden_name' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_number' => 'nullable|string',
            'emergency_relation' => 'nullable|string',
            'parent_spouse_guardian' => 'nullable|string',
            'parent_spouse_guardian_address' => 'nullable|string',
            'personal_data_sheet_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'elementary_school' => 'nullable|string',
            'elementary_address' => 'nullable|string',
            'elementary_year_graduated' => 'nullable|string',
            'high_school' => 'nullable|string',
            'high_school_address' => 'nullable|string',
            'high_school_year_graduated' => 'nullable|string',
            'college_name' => 'nullable|string',
            'college_address' => 'nullable|string',
            'college_course' => 'nullable|string',
            'college_year' => 'nullable|string',
            // Academic information
            'school_year' => 'nullable|string',
            'semester' => 'nullable|string',
            'student_type' => 'nullable|in:new,old',
            'department_id' => 'required|integer',
            'course_id' => 'required|integer',
            'organization_id' => 'nullable|integer',
            'scholarship_id' => 'nullable|integer',
            'year_level' => 'required|integer',
            'student_type1' => 'required|in:regular,irregular,transferee',
            'student_type2' => 'required|in:paying,scholar',
            // Entrance credentials
            'form_137_presented' => 'nullable|boolean',
            'tor_presented' => 'nullable|boolean',
            'good_moral_cert_presented' => 'nullable|boolean',
            'birth_cert_presented' => 'nullable|boolean',
            'marriage_cert_presented' => 'nullable|boolean',
        ]);

        // Use the email provided in the form
        $email = $validated['email'] ?? null;

        // Generate username and temp password
        $username = $email; // Username is the email address
        // Use default password "password"
        $tempPassword = 'password';

        // Guarantee only valid enum values for gender
        $genderMap = [
            'M' => 'male', 'F' => 'female', 'O' => 'other',
            'm' => 'male', 'f' => 'female', 'o' => 'other',
            'male' => 'male', 'female' => 'female', 'other' => 'other'
        ];
        $genderValue = $validated['gender'] ?? null;
        $gender = $genderMap[$genderValue] ?? 'other';

        // Automatically assign department-related organization
        // Find organization with matching department_id
        $departmentOrganization = \App\Models\Organization::where('department_id', $validated['department_id'])->first();
        $organizationId = null;
        
        if ($departmentOrganization) {
            // Automatically assign department-related organization
            $organizationId = $departmentOrganization->id;
        } else {
            // If no department-related organization exists, use manually selected one (for non-academic)
            $organizationId = $validated['organization_id'] ?? null;
        }

        try {
            // Handle PWD ID image upload if provided
            $pwdIdImagePath = null;
            if ($request->hasFile('pwd_id_image')) {
                $pwdImage = $request->file('pwd_id_image');
                $pwdImageName = 'pwd_id_' . $validated['user_id'] . '_' . time() . '.' . $pwdImage->getClientOriginalExtension();
                $pwdIdImagePath = $pwdImage->storeAs('students/pwd_ids', $pwdImageName, 'public');
            }

            // Create user with only basic fields
            $user = \App\Models\User::withoutEvents(function() use ($validated, $email, $gender, $organizationId, $tempPassword) {
                return \App\Models\User::create([
                    'user_id' => $validated['user_id'],
                    'first_name' => $validated['first_name'],
                    'middle_name' => $validated['middle_name'] ?? '',
                    'last_name' => $validated['last_name'],
                    'ext_name' => $validated['ext_name'] ?? null,
                    'email' => $email,
                    'gender' => $gender,
                    'birth_date' => $validated['birth_date'] ?? null,
                    'contact_number' => $validated['contact_number'] ?? '',
                    'tel_no' => $validated['tel_no'] ?? null,
                    'department_id' => $validated['department_id'],
                    'course_id' => $validated['course_id'],
                    'organization_id' => $organizationId,
                    'role' => 1,
                    'password' => bcrypt($tempPassword),
                ]);
            });

            // Save to normalized tables
            $this->saveNormalizedData($user, $validated, $pwdIdImagePath);

        // Handle personal data sheet image upload if provided
        $imagePath = null;
        if ($request->hasFile('personal_data_sheet_image')) {
            $image = $request->file('personal_data_sheet_image');
            $imageName = 'personal_data_sheet_' . $validated['user_id'] . '_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('students/personal_data_sheets', $imageName, 'public');
            // Update document checklist with image path
            $user->documentChecklist()->update(['personal_data_sheet_image' => $imagePath]);
        }

        // Create Student record - only include columns that exist in student_information table
        // Note: first_name, last_name, email, etc. are in users table, not student_information
        $studentData = [
            'user_id' => $user->id,
            'student_id' => $user->user_id, // Copy user_id as student_id
        ];
        
        // Add student information fields if they exist
        if ($user->studentInformation) {
            $studentData['year_level'] = $user->studentInformation->year_level ?? null;
            $studentData['student_type1'] = $user->studentInformation->student_type1 ?? null;
            $studentData['student_type2'] = $user->studentInformation->student_type2 ?? null;
            $studentData['student_type'] = $user->studentInformation->student_type ?? null;
            $studentData['school_year'] = $user->studentInformation->school_year ?? null;
            $studentData['semester'] = $user->studentInformation->semester ?? null;
            $studentData['academic_year'] = $user->studentInformation->academic_year ?? null;
            $studentData['scholarship_id'] = $user->studentInformation->scholarship_id ?? $validated['scholarship_id'] ?? null;
            $studentData['is_active_scholar'] = $user->studentInformation->is_active_scholar ?? false;
            $studentData['scholarship_grant_name'] = $user->studentInformation->scholarship_grant_name ?? null;
        } else {
            // If studentInformation doesn't exist yet, use validated data
            $studentData['scholarship_id'] = $validated['scholarship_id'] ?? null;
        }
        
        $student = Student::create($studentData);

            // Send email notification
            try {
                $name = $user->first_name . ' ' . $user->last_name;
                Mail::to($user->email)->send(new AccountCredentialsMail(
                    $user->email,
                    $tempPassword,
                    $name,
                    'Student'
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send email notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue even if email fails
            }

            \Illuminate\Support\Facades\Log::info('Student created successfully', [
                'student_id' => $student->id,
                'user_id' => $user->id,
                'created_by' => auth()->id(),
                'timestamp' => now(),
            ]);

            return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')->with('success', 'Student added and notified successfully.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create student', [
                'user_id' => $request->user_id ?? null,
                'email' => $request->email ?? null,
                'error' => $e->getMessage(),
                'created_by' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create student. Please try again or contact support if the problem persists.');
        }
    }

    // Show edit form for a student
    public function edit($student)
    {
        $student = Student::with([
            'user', 
            'department', 
            'course', 
            'organization', 
            'scholarship', 
            'user.personalInformation.nationality',
            'user.emergencyContacts',
            'user.addresses', // Load addresses for address restoration
            'user.studentInformation',
            'user.familyMembers', // Use plural - this is the relationship
            'user.educationalBackgrounds', // Use plural - this is the relationship
            'user.pwdInformation',
            'user.indigenousMember',
            'user.governmentAffiliation',
            'user.fraternityMember',
            'user.documentChecklist' // Load document checklist for entrance credentials
        ])->findOrFail($student);
        // Use cached reference data
        $departments = \App\Services\CacheService::getDepartments();
        $courses = \App\Services\CacheService::getCourses();
        $organizations = \App\Services\CacheService::getOrganizations();
        $scholarships = \App\Services\CacheService::getScholarships();
        $nationalities = \App\Models\Nationality::where('is_active', true)->orderBy('name')->get();
        return view('admin.staff.edit-student', compact('student', 'departments', 'courses', 'organizations', 'scholarships', 'nationalities'));
    }

    // Update student details
    public function update(Request $request, $student)
    {
        $student = Student::with(['user', 'department', 'course', 'organization', 'scholarship'])->findOrFail($student);
        $user = $student->user;
        
        if (!$user) {
            return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')
                ->with('error', 'Associated user record not found.');
        }
        
        $validated = $request->validate([
            'user_id' => ['required', 'unique:users,user_id,' . $user->id, new \App\Rules\UserIdByRole(1)],
            'first_name' => 'required',
            'last_name' => 'required',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            // Email validation - required and must be unique (except for current user)
            'email' => 'required|email|unique:users,email,' . $user->id,
            'gender' => 'nullable|in:male,female,other',
            'age' => 'nullable|integer|min:1|max:100',
            'civil_status' => 'nullable|in:single,married,divorced,widowed',
            'maiden_name' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'place_of_birth' => 'nullable|string',
            'nationality_id' => 'nullable|exists:nationalities,id',
            'nationality' => 'nullable|string|max:255',
            'religion' => 'nullable|string',
            'complete_home_address' => 'nullable|string',
            'is_pwd' => 'nullable|boolean',
            'disability_type' => 'nullable|string|max:255',
            'pwd_id_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:102400',
            'is_indigenous_group_member' => 'nullable|boolean',
            'indigenous_group_specify' => 'nullable|string|max:255',
            'is_government_member' => 'nullable|in:no,yes',
            'government_level' => 'nullable|in:barangay,municipal_city,provincial,national',
            'government_role_position' => 'nullable|string',
            'fraternity_sorority_name' => 'nullable|string|max:255',
            'fraternity_sorority_position' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string',
            'parent_spouse_guardian' => 'nullable|string',
            'parent_spouse_guardian_address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_number' => 'nullable|string',
            'emergency_relation' => 'nullable|string',
            'personal_data_sheet_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
            'elementary_school' => 'nullable|string',
            'elementary_address' => 'nullable|string',
            'elementary_year_graduated' => 'nullable|string',
            'high_school' => 'nullable|string',
            'high_school_address' => 'nullable|string',
            'high_school_year_graduated' => 'nullable|string',
            'college_name' => 'nullable|string',
            'college_address' => 'nullable|string',
            'college_course' => 'nullable|string',
            'college_year' => 'nullable|string',
            'school_year' => 'nullable|string',
            'semester' => 'nullable|string',
            'student_type' => 'nullable|in:new,old',
            'department_id' => 'required|integer',
            'course_id' => 'required|integer',
            'organization_id' => 'nullable|integer',
            'scholarship_id' => 'nullable|integer',
            'year_level' => 'nullable|integer',
            'student_type1' => 'nullable|in:regular,irregular,transferee',
            'student_type2' => 'nullable|in:paying,scholar',
            'form_137_presented' => 'nullable|boolean',
            'tor_presented' => 'nullable|boolean',
            'good_moral_cert_presented' => 'nullable|boolean',
            'birth_cert_presented' => 'nullable|boolean',
            'marriage_cert_presented' => 'nullable|boolean',
        ]);

        // Use the email provided in the form
        $email = $validated['email'] ?? $user->email;
        
        // Check if email has changed
        $emailChanged = strtolower(trim($user->email)) !== strtolower(trim($email));
        
        // Check if name or user_id changed (which would change the email)
        $nameOrIdChanged = (
            strtolower(trim($user->first_name)) !== strtolower(trim($validated['first_name'])) ||
            strtolower(trim($user->last_name)) !== strtolower(trim($validated['last_name'])) ||
            $user->user_id !== $validated['user_id']
        );
        
        // Check if resend verification is requested
        $resendVerification = $request->has('resend_verification') && $request->input('resend_verification') == '1';
        
        // Check if department has changed
        $departmentChanged = $user->department_id != $validated['department_id'];
        
        // Get current organization
        $currentOrganization = $user->organization;
        $isDepartmentRelatedOrg = $currentOrganization && $currentOrganization->department_id !== null;
        
        // Handle organization assignment
        $organizationId = null;
        
        if ($departmentChanged) {
            // Department changed - find new department-related organization
            $newDepartmentOrganization = \App\Models\Organization::where('department_id', $validated['department_id'])->first();
            if ($newDepartmentOrganization) {
                $organizationId = $newDepartmentOrganization->id;
            } else {
                // No department-related organization for new department, allow manual selection (for non-academic)
                $organizationId = $validated['organization_id'] ?? null;
            }
        } else {
            // Department hasn't changed
            if ($isDepartmentRelatedOrg) {
                // Current organization is department-related - keep it (cannot be manually edited)
                $organizationId = $user->organization_id;
            } else {
                // Current organization is not department-related - allow manual editing
                $organizationId = $validated['organization_id'] ?? null;
            }
        }
        
        // Generate temporary password if email changed OR resend verification is requested
        $tempPassword = null;
        if ($emailChanged || $resendVerification || $nameOrIdChanged) {
            // Generate temporary password: last_name@user_id (lowercase), e.g., datu@2023304529
            $tempPassword = strtolower($validated['last_name']) . '@' . $validated['user_id'];
        }
        
        // Generate username (email address)
        $username = $email;

        // Guarantee only valid enum values for gender
        $genderMap = [
            'M' => 'male', 'F' => 'female', 'O' => 'other',
            'm' => 'male', 'f' => 'female', 'o' => 'other',
            'male' => 'male', 'female' => 'female', 'other' => 'other'
        ];
        $genderValue = $validated['gender'] ?? null;
        $gender = $genderMap[$genderValue] ?? 'other';

        // Handle personal data sheet image upload if provided
        $imagePath = $user->documentChecklist->personal_data_sheet_image ?? null; // Keep existing image if not updated
        if ($request->hasFile('personal_data_sheet_image')) {
            // Delete old image if it exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            $image = $request->file('personal_data_sheet_image');
            $imageName = 'personal_data_sheet_' . $validated['user_id'] . '_' . time() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('students/personal_data_sheets', $imageName, 'public');
        }

        try {
            // Prepare user update data (only basic fields)
            $userUpdateData = [
                'user_id' => $validated['user_id'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? '',
                'last_name' => $validated['last_name'],
                'email' => $email,
                'gender' => $gender,
                'birth_date' => $validated['birth_date'] ?? null,
                'contact_number' => $validated['contact_number'] ?? '',
                'department_id' => $validated['department_id'],
                'course_id' => $validated['course_id'],
                'organization_id' => $organizationId,
            ];
            
            // Add password update if email changed OR resend verification is requested
            if (($emailChanged || $resendVerification) && $tempPassword) {
                $userUpdateData['password'] = bcrypt($tempPassword);
            }
            
            // Update User table without triggering observer (we'll update Student manually)
            \App\Models\User::withoutEvents(function() use ($user, $userUpdateData) {
                $user->update($userUpdateData);
            });

            // Handle PWD ID image upload if provided
            $pwdIdImagePath = null;
            if ($request->hasFile('pwd_id_image')) {
                $pwdImage = $request->file('pwd_id_image');
                $pwdImageName = 'pwd_id_' . $validated['user_id'] . '_' . time() . '.' . $pwdImage->getClientOriginalExtension();
                $pwdIdImagePath = $pwdImage->storeAs('students/pwd_ids', $pwdImageName, 'public');
            }

            // Update normalized tables
            $this->updateNormalizedData($user, $validated, $pwdIdImagePath, false);

        // Update Student table (minimal - data is in normalized tables)
        $student->update([
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? '',
            'last_name' => $validated['last_name'],
            'email' => $email,
            'gender' => $gender,
            'birth_date' => $validated['birth_date'] ?? null,
            'contact_number' => $validated['contact_number'] ?? '',
            'department_id' => $validated['department_id'],
            'course_id' => $validated['course_id'],
            'organization_id' => $organizationId,
            'scholarship_id' => $validated['scholarship_id'] ?? null,
        ]);
            
            // Send email with temporary password if email changed OR resend verification is requested
            if (($emailChanged || $resendVerification) && $tempPassword) {
                try {
                    $name = $user->first_name . ' ' . $user->last_name;
                    // Use AccountCredentialsMail for consistent email formatting
                    Mail::to($user->email)->send(new \App\Mail\AccountCredentialsMail(
                        $user->email,
                        $tempPassword,
                        $name,
                        'Student'
                    ));
                    
                    // Increment verification email count if resending
                    if ($resendVerification) {
                        $user->verification_email_count = ($user->verification_email_count ?? 0) + 1;
                        $user->save();
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Failed to send email notification', [
                        'user_id' => $user->id,
                        'email_changed' => $emailChanged,
                        'resend_verification' => $resendVerification,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue even if email fails
                }
            }

            \Illuminate\Support\Facades\Log::info('Student updated successfully', [
                'student_id' => $student->id,
                'user_id' => $user->id,
                'updated_by' => auth()->id(),
                'timestamp' => now(),
            ]);

            $successMessage = 'Student updated successfully.';
            if ($resendVerification) {
                $successMessage .= ' Verification email with temporary password has been sent.';
            } elseif ($emailChanged) {
                $successMessage .= ' Verification email with temporary password has been sent to the new email address.';
            }
            
            return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')->with('success', $successMessage);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update student', [
                'student_id' => $student->id,
                'user_id' => $user->id ?? null,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update student. Please try again or contact support if the problem persists.');
        }
    }

    // Delete a student
    public function destroy($student)
    {
        $student = Student::findOrFail($student);
        $student->delete();
    return redirect()->route('admin.staff.dashboard.AdmissionServicesOfficer.student-management')->with('success', 'Student deleted successfully.');
    }

    // Resend verification email to student
    public function resendVerificationEmail($student)
    {
        try {
            $student = Student::with('user')->findOrFail($student);
            $user = $student->user;
            
            // If user relationship doesn't exist, try to find by user_id
            if (!$user && $student->user_id) {
                $user = \App\Models\User::find($student->user_id);
            }
            
            if (!$user) {
                return redirect()->back()->with('error', 'User record not found for this student. Please ensure the student has a corresponding user account.');
            }
            
            // Validate email exists
            if (empty($user->email)) {
                return redirect()->back()->with('error', 'Student email address is missing. Cannot send verification email.');
            }
            
            // Generate temporary password: last_name@user_id (lowercase)
            $lastName = $user->last_name ?? 'user';
            $userId = $user->user_id ?? $user->id;
            $tempPassword = strtolower($lastName) . '@' . $userId;
            
            // Update password
            $user->password = bcrypt($tempPassword);
            
            // Increment verification email count
            $user->verification_email_count = ($user->verification_email_count ?? 0) + 1;
            $user->save();
            
            // Send verification email using AccountCredentialsMail for consistency
            try {
                $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if (empty($name)) {
                    $name = 'Student';
                }
                
                Mail::to($user->email)->send(new AccountCredentialsMail(
                    $user->email,
                    $tempPassword,
                    $name,
                    'Student'
                ));
                
                \Illuminate\Support\Facades\Log::info('Verification email resent', [
                    'student_id' => $student->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'verification_email_count' => $user->verification_email_count,
                    'sent_by' => auth()->id(),
                    'timestamp' => now(),
                ]);
                
                return redirect()->back()->with('success', "Verification email sent successfully. (Sent {$user->verification_email_count} time(s) to this email address)");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send verification email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
                
                // Rollback the count increment if email failed
                $user->verification_email_count = max(0, ($user->verification_email_count ?? 1) - 1);
                $user->save();
                
                return redirect()->back()->with('error', 'Failed to send verification email. Please try again.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to resend verification email', [
                'student_id' => $student->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()->with('error', 'Failed to resend verification email. Please try again.');
        }
    }

    // Show student details
    public function show($id)
    {
        $student = Student::with([
            'user', 
            'department', 
            'course', 
            'organization', 
            'scholarship',
            'user.personalInformation.nationality',
            'user.pwdInformation',
            'user.indigenousMember',
            'user.governmentAffiliation',
            'user.fraternityMember'
        ])->findOrFail($id);
        return view('admin.staff.dashboard.AdmissionServicesOfficer.student-details', compact('student'));
    }
}
