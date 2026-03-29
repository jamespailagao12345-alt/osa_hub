<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Address;
use App\Models\EmergencyContact;
use App\Models\StudentInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create()
    {
        // Registration is disabled
        abort(403, 'Student self-registration is disabled. Please contact the Admission Services Officer.');
    }

    /**
     * Handle account creation.
     */
    public function store(Request $request)
    {
        // Optional: Disable public registration
        // abort(403, 'Registration is closed.');

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Generate email in format: first_name.last_name+student_id@gmail.com
        $generatedEmail = \App\Helpers\EmailHelper::generateStudentEmail(
            $request->first_name,
            $request->last_name,
            $request->user_id
        );
        
        // Create user with only basic fields
        $user = User::create([
            'user_id' => $request->user_id,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $generatedEmail,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'department_id' => $request->department_id,
            'course_id' => $request->course_id,
            'organization_id' => $request->organization_id,
            'contact_number' => $request->contact_number,
            'password' => Hash::make($request->password),
            'role' => 1, // Default: student
        ]);

        // Save to normalized tables
        // Emergency Contact
        if ($request->emergency_contact_name) {
            EmergencyContact::create([
                'user_id' => $user->id,
                'name' => $request->emergency_contact_name,
                'contact_number' => $request->emergency_contact_number ?? '',
                'relation' => $request->emergency_relation ?? null,
            ]);
        }

        // Student Information
        if ($request->year_level || $request->student_type1 || $request->student_type2) {
            StudentInformation::create([
                'user_id' => $user->id,
                'year_level' => $request->year_level ?? null,
                'student_type1' => $request->student_type1 ?? null,
                'student_type2' => $request->student_type2 ?? null,
                'scholarship_id' => $request->scholarship_id ?? null,
            ]);
        }

        // One-time QR code generation using user details
        $qrPayload = [
            'student_id' => $user->user_id,
            'first_name' => $user->first_name,
            'middle_name' => $user->middle_name,
            'last_name' => $user->last_name,
            'department' => optional($user->department)->name,
            'course' => optional($user->course)->name,
            'year_level' => optional($user->studentInformation)->year_level,
            'generated_at' => now()->toIso8601String(),
        ];
        $qrData = json_encode($qrPayload);
        // Store PNG in public storage under qr-codes/{user_id}.png
    $svg = \QrCode::format('svg')->size(300)->generate($qrData);
    \Storage::disk('public')->put("qr-codes/{$user->user_id}.svg", $svg);

        // Automatically create Student record from User record
        $this->syncUserToStudent($user);

        // Log the user in automatically
        auth()->login($user);

        return redirect('/student/dashboard')->with('success', 'Account created successfully!');
    }
    
    /**
     * Sync a User record (role = 1) to Student table
     * Note: This method is simplified since data is now in normalized tables
     */
    private function syncUserToStudent(User $user)
    {
        if ($user->role != 1) {
            return; // Only sync students
        }
        
        // Check if Student record already exists
        $existingStudent = \App\Models\Student::where('user_id', $user->id)->first();
        
        if ($existingStudent) {
            // Update existing Student record with basic User data
            $existingStudent->update([
                'first_name' => $user->first_name ?? $existingStudent->first_name,
                'middle_name' => $user->middle_name ?? $existingStudent->middle_name,
                'last_name' => $user->last_name ?? $existingStudent->last_name,
                'email' => $user->email ?? $existingStudent->email,
                'contact_number' => $user->contact_number ?? $existingStudent->contact_number,
                'gender' => $user->gender ?? $existingStudent->gender,
                'birth_date' => $user->birth_date ?? $existingStudent->birth_date,
                'department_id' => $user->department_id ?? $existingStudent->department_id,
                'course_id' => $user->course_id ?? $existingStudent->course_id,
                'organization_id' => $user->organization_id ?? $existingStudent->organization_id,
                'scholarship_id' => optional($user->studentInformation)->scholarship_id ?? $existingStudent->scholarship_id,
            ]);
            return $existingStudent;
        }
        
        // Create new Student record - only include columns that exist in student_information table
        // Note: first_name, last_name, email, etc. are in users table, not student_information
        $studentData = [
            'user_id' => $user->id,
            'student_id' => $user->user_id ?? null, // Copy user_id as student_id if exists
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
            $studentData['scholarship_id'] = $user->studentInformation->scholarship_id ?? null;
            $studentData['is_active_scholar'] = $user->studentInformation->is_active_scholar ?? false;
            $studentData['scholarship_grant_name'] = $user->studentInformation->scholarship_grant_name ?? null;
        }
        
        $student = \App\Models\Student::create($studentData);
        
        return $student;
    }
}