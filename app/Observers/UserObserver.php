<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use App\Models\AssistantAssignment;

class UserObserver
{
    private static $syncing = false;

    /**
     * Handle the User "created" event.
     * Sync users to appropriate tables based on role:
     * - Role 4 (admin) and Role 2 (staff) → staff_information
     * - Role 1 (student) → student_information
     * - Role 3 (assistant) → student_information AND student_leaders_information
     */
    public function created(User $user)
    {
        // Prevent infinite recursion
        if (self::$syncing) {
            return;
        }
        
        $this->syncUserToAppropriateTables($user);
    }

    /**
     * Handle the User "updated" event.
     * Sync users to appropriate tables and handle email changes.
     */
    public function updated(User $user)
    {
        // Prevent infinite recursion
        if (self::$syncing) {
            return;
        }
        
        // Sync user to appropriate tables
        $this->syncUserToAppropriateTables($user);
        
        // Handle email changes for staff (role 2 and 4)
        if (in_array($user->role, [2, 4]) && $user->wasChanged('email')) {
            $this->handleStaffEmailChange($user);
        }
    }

    /**
     * Sync user to appropriate tables based on role
     */
    private function syncUserToAppropriateTables(User $user)
    {
        if (self::$syncing) {
            return;
        }
        
        self::$syncing = true;
        
        try {
            $role = (int) $user->role;
            
            // Role 4 (admin) and Role 2 (staff) → staff_information
            if ($role === 4 || $role === 2) {
                $this->syncUserToStaff($user);
            }
            
            // Role 1 (student) → student_information
            if ($role === 1) {
                $this->syncUserToStudent($user);
            }
            
            // Role 3 (assistant) → student_information AND student_leaders_information
            if ($role === 3) {
                // Sync to student_information (assistants are also students)
                $this->syncUserToStudent($user);
                
                // Sync to student_leaders_information
                $this->syncUserToStudentLeaders($user);
            }
        } finally {
            self::$syncing = false;
        }
    }

    /**
     * Handle email changes for staff (role 2 and 4)
     */
    private function handleStaffEmailChange(User $user)
    {
        self::$syncing = true;
        
        try {
            $oldEmail = $user->getOriginal('email');
            $newEmail = $user->email;
            
            // Find staff by old email (case-insensitive)
            $staff = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($oldEmail))])->first();
            
            // If not found by old email, try new email (case-insensitive)
            if (!$staff) {
                $staff = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($newEmail))])->first();
            }
            
            // If still not found, try by user_id
            if (!$staff && $user->user_id) {
                $staff = Staff::where('user_id', $user->user_id)->first();
            }
            
            // Update staff email if found and different
            if ($staff && strtolower(trim($staff->email)) !== strtolower(trim($newEmail))) {
                $staff->email = $newEmail;
                $staff->saveQuietly(); // Use saveQuietly to prevent observer recursion
            }
            
            // Update ALL other user records with the old email to the new email
            if ($oldEmail && strtolower(trim($newEmail)) !== strtolower(trim($oldEmail))) {
                User::withoutEvents(function() use ($oldEmail, $newEmail, $user) {
                    User::whereRaw('LOWER(email) = ?', [strtolower(trim($oldEmail))])
                        ->where('id', '!=', $user->id)
                        ->update(['email' => $newEmail]);
                });
            }
            
            // Update ALL staff records with the old email to the new email
            if ($oldEmail && strtolower(trim($newEmail)) !== strtolower(trim($oldEmail))) {
                Staff::withoutEvents(function() use ($oldEmail, $newEmail, $staff) {
                    Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($oldEmail))])
                        ->where('id', '!=', $staff->id ?? 0)
                        ->update(['email' => $newEmail]);
                });
            }
        } finally {
            self::$syncing = false;
        }
    }

    /**
     * Sync a User record (role = 2 or 4) to Staff table (staff_information)
     */
    private function syncUserToStaff(User $user)
    {
        $role = (int) $user->role;
        if ($role !== 2 && $role !== 4) {
            return; // Only sync staff and admin
        }
        
        // Prevent infinite recursion
        if (self::$syncing) {
            return;
        }
        
        self::$syncing = true;
        
        try {
            // Check if Staff record already exists
            $existingStaff = Staff::where('user_id', $user->id)
                ->orWhereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
                ->first();
            
            $staffData = [
                'first_name' => $user->first_name ?? '',
                'last_name' => $user->last_name ?? '',
                'middle_name' => $user->middle_name ?? '',
                'user_id' => $user->user_id,
                'employee_id' => $user->user_id, // Copy 7-digit user_id as employee_id
                'email' => $user->email,
                'designation' => $user->designation ?? null,
                'department_id' => $user->department_id ?? null,
                'organization_id' => $user->organization_id ?? null,
                'contact_number' => $user->contact_number ?? null,
                'birth_date' => $user->birth_date ?? null,
                'gender' => $user->gender ?? null,
                'age' => $user->age ?? null,
            ];
            
            // Add optional fields if they exist in the user model
            if (isset($user->image)) {
                $staffData['image'] = $user->image;
            }
            if (isset($user->service_order)) {
                $staffData['service_order'] = $user->service_order;
            }
            if (isset($user->length_of_service)) {
                $staffData['length_of_service'] = $user->length_of_service;
            }
            if (isset($user->contract_end_at)) {
                $staffData['contract_end_at'] = $user->contract_end_at;
            }
            if (isset($user->employment_status)) {
                $staffData['employment_status'] = $user->employment_status;
            }
            if (isset($user->about_me)) {
                $staffData['about_me'] = $user->about_me;
            }
            
            if ($existingStaff) {
                // Update existing Staff record
                $existingStaff->update($staffData);
            } else {
                // Create new Staff record
                // For admin_id, use the user's id if role is 4, otherwise find admin
                if ($role === 4) {
                    $staffData['admin_id'] = $user->id; // Admin is their own admin
                } else {
                    // Try to find an admin user
                    $admin = User::where('role', 4)->first();
                    $staffData['admin_id'] = $admin ? $admin->id : $user->id;
                }
                
                Staff::create($staffData);
            }
        } finally {
            self::$syncing = false;
        }
    }

    /**
     * Sync a User record (role = 1 or 3) to Student table (student_information)
     * Role 3 users are also students, so they need to be in student_information
     */
    private function syncUserToStudent(User $user)
    {
        $role = (int) $user->role;
        if ($role !== 1 && $role !== 3) {
            return; // Only sync students and assistants
        }
        
        // Prevent infinite recursion
        if (self::$syncing) {
            return;
        }
        
        self::$syncing = true;
        
        try {
            // Check if Student record already exists
            $existingStudent = Student::where('user_id', $user->id)->first();
            
            // Use the email from the user record
            $email = $user->email ?? null;
            
            // Get student information from normalized table if available
            $studentInfo = $user->studentInformation;
            
            $studentData = [
                'user_id' => $user->id,
                'student_id' => $user->user_id, // Copy 10-digit user_id as student_id
                'first_name' => $user->first_name ?? '',
                'middle_name' => $user->middle_name ?? '',
                'last_name' => $user->last_name ?? '',
                'email' => $email,
                'contact_number' => $user->contact_number ?? '',
                'gender' => $user->gender ?? 'other',
                'birth_date' => $user->birth_date ?? null,
                'department_id' => $user->department_id ?? null,
                'course_id' => $user->course_id ?? null,
                'organization_id' => $user->organization_id ?? null,
                'scholarship_id' => optional($studentInfo)->scholarship_id ?? $user->scholarship_id ?? null,
                'year_level' => optional($studentInfo)->year_level ?? null,
                'student_type1' => optional($studentInfo)->student_type1 ?? null,
                'student_type2' => optional($studentInfo)->student_type2 ?? null,
            ];
            
            // Add optional fields if they exist in the user model
            if (isset($user->ext_name)) {
                $studentData['ext_name'] = $user->ext_name;
            }
            if (isset($user->tel_no)) {
                $studentData['tel_no'] = $user->tel_no;
            }
            if (isset($user->age)) {
                $studentData['age'] = $user->age;
            }
            if (isset($user->civil_status)) {
                $studentData['civil_status'] = $user->civil_status;
            }
            if (isset($user->maiden_name)) {
                $studentData['maiden_name'] = $user->maiden_name;
            }
            if (isset($user->place_of_birth)) {
                $studentData['place_of_birth'] = $user->place_of_birth;
            }
            if (isset($user->nationality)) {
                $studentData['nationality'] = $user->nationality;
            }
            if (isset($user->religion)) {
                $studentData['religion'] = $user->religion;
            }
            if (isset($user->complete_home_address)) {
                $studentData['complete_home_address'] = $user->complete_home_address;
            }
            if (isset($user->street)) {
                $studentData['street'] = $user->street;
            }
            if (isset($user->barangay)) {
                $studentData['barangay'] = $user->barangay;
            }
            if (isset($user->city_municipality)) {
                $studentData['city_municipality'] = $user->city_municipality;
            }
            if (isset($user->province)) {
                $studentData['province'] = $user->province;
            }
            if (isset($user->zip_code)) {
                $studentData['zip_code'] = $user->zip_code;
            }
            if (isset($user->student_type)) {
                $studentData['student_type'] = $user->student_type;
            }
            if (isset($user->school_year)) {
                $studentData['school_year'] = $user->school_year;
            }
            if (isset($user->semester)) {
                $studentData['semester'] = $user->semester;
            }
            if (isset($user->emergency_contact_name)) {
                $studentData['emergency_contact_name'] = $user->emergency_contact_name;
            }
            if (isset($user->emergency_contact_number)) {
                $studentData['emergency_contact_number'] = $user->emergency_contact_number;
            }
            if (isset($user->emergency_relation)) {
                $studentData['emergency_relation'] = $user->emergency_relation;
            }
            if (isset($user->image)) {
                $studentData['personal_data_sheet_image'] = $user->image;
            }
            
            if ($existingStudent) {
                // Update existing Student record
                $existingStudent->update($studentData);
            } else {
                // Create new Student record
                Student::create($studentData);
            }
        } finally {
            self::$syncing = false;
        }
    }

    /**
     * Sync a User record (role = 3) to Student Leaders table (student_leaders_information)
     * All role 3 users must be in student_leaders_information
     */
    private function syncUserToStudentLeaders(User $user)
    {
        $role = (int) $user->role;
        if ($role !== 3) {
            return; // Only sync assistants
        }
        
        // Prevent infinite recursion
        if (self::$syncing) {
            return;
        }
        
        self::$syncing = true;
        
        try {
            // Check if AssistantAssignment record already exists
            $existingAssignment = AssistantAssignment::where('user_id', $user->id)->first();
            
            $assignmentData = [
                'user_id' => $user->id,
                'organization_id' => $user->organization_id ?? null,
                'department_id' => $user->department_id ?? null,
                'supervisor_id' => $user->supervisor_id ?? null,
                'active' => true, // Default to active
            ];
            
            // Add position if it exists in user model
            if (isset($user->position)) {
                $assignmentData['position'] = $user->position;
            }
            
            if ($existingAssignment) {
                // Update existing assignment
                $existingAssignment->update($assignmentData);
            } else {
                // Create new assignment
                AssistantAssignment::create($assignmentData);
            }
        } finally {
            self::$syncing = false;
        }
    }
}

