<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Scholarship;
use App\Models\EmergencyContact;
use App\Models\StudentInformation;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    // Show registration form with departments & scholarships
    public function showRegistrationForm()
    {
        // Registration is disabled
        abort(403, 'Student self-registration is disabled. Please contact the Admission Services Officer.');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'user_id' => ['required', 'string', 'unique:users', new \App\Rules\UserIdByRole(1)],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
            'gender' => ['required', 'in:male,female,other'],
            'birth_date' => ['required', 'date', 'before:today'],
            'department_id' => ['required', 'exists:departments,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'organization_id' => ['required', 'exists:organizations,id'],
            'year_level' => ['required', 'integer', 'min:1', 'max:5'],
            'student_type1' => ['required', 'in:regular,irregular,transferee'],
            'student_type2' => ['required', 'in:paying,scholar'],
            'scholarship_id' => ['nullable', 'exists:scholarships,id'],
            'contact_number' => ['required', 'string', 'max:20'],
            'emergency_contact_name' => ['required', 'string', 'max:100'],
            'emergency_contact_number' => ['required', 'string', 'max:20'],
            'emergency_relation' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'agreement' => ['required'],
        ], [
            'agreement.required' => 'You must agree to the Data Privacy Agreement.',
        ]);
    }

    protected function create(array $data)
    {
        // Create user with only basic fields
        $user = User::create([
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'gender' => $data['gender'],
            'birth_date' => $data['birth_date'],
            'department_id' => $data['department_id'],
            'course_id' => $data['course_id'],
            'organization_id' => $data['organization_id'],
            'contact_number' => $data['contact_number'],
            'role' => 1, // student
            'password' => Hash::make($data['password']),
        ]);

        // Save to normalized tables
        // Emergency Contact
        EmergencyContact::create([
            'user_id' => $user->id,
            'name' => $data['emergency_contact_name'],
            'contact_number' => $data['emergency_contact_number'],
            'relation' => $data['emergency_relation'],
        ]);

        // Student Information
        StudentInformation::create([
            'user_id' => $user->id,
            'year_level' => $data['year_level'],
            'student_type1' => $data['student_type1'],
            'student_type2' => $data['student_type2'],
            'scholarship_id' => ($data['student_type2'] === 'scholar') ? ($data['scholarship_id'] ?? null) : null,
        ]);

        return $user;
    }

    // 👇 ADD THIS METHOD HERE 👇
    protected function registered(Request $request, User $user)
    {
        return redirect('/login')->with('success', 'Registration successful! Please check your email to verify your account.');
    }
    
}