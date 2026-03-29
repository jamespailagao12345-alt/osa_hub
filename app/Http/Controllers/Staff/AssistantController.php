<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistantController extends Controller
{
    // List assistants belonging to the current staff
    public function index(Request $request)
    {
        $status = $request->query('status', 'all'); // all | active | suspended

        // Get assistants from assistant_assignments table (includes role 1 and role 3)
        $assignmentIds = \App\Models\AssistantAssignment::where('supervisor_id', auth()->id())
            ->pluck('user_id')
            ->unique();

        $query = User::whereIn('id', $assignmentIds);

        if ($status === 'active') {
            $query->where(function ($q) {
                $q->whereNull('suspended')->orWhere('suspended', false);
            });
            // Also check active status in assistant_assignments
            $activeAssignmentIds = \App\Models\AssistantAssignment::where('supervisor_id', auth()->id())
                ->where('active', true)
                ->pluck('user_id')
                ->unique();
            $query->whereIn('id', $activeAssignmentIds);
        } elseif ($status === 'suspended') {
            $query->where('suspended', true);
        }

        $assistants = $query->orderBy('last_name')->orderBy('first_name')->get();

        return view('staff.assistants.index', compact('assistants', 'status'));
    }

    public function create(Request $request)
    {
        // Use cached reference data
        $departments = \App\Services\CacheService::getDepartments();
        $organizations = \App\Services\CacheService::getOrganizations();
        $selectedOrganizationId = $request->query('organization_id');
        
        // Get selected organization and its department if academic
        $selectedOrganization = null;
        $courses = \App\Models\Course::orderBy('name')->get();
        
        if ($selectedOrganizationId) {
            $selectedOrganization = \App\Models\Organization::find($selectedOrganizationId);
            
            // If organization is academic (has department_id), filter courses by that department
            if ($selectedOrganization && $selectedOrganization->department_id) {
                $courses = \App\Models\Course::where('department_id', $selectedOrganization->department_id)
                    ->orderBy('name')
                    ->get();
            }
        }
        
        return view('staff.assistants.create', compact('departments', 'organizations', 'selectedOrganizationId', 'selectedOrganization', 'courses'));
    }

    /**
     * Fetch past organizations for a user by user_id or email
     * Used to auto-populate leadership background
     */
    public function fetchPastOrganizations(Request $request)
    {
        $user_id = $request->input('user_id');
        $email = $request->input('email');
        $currentOrganizationId = $request->input('current_organization_id');
        
        if (!$user_id && !$email) {
            return response()->json(['past_organizations' => []]);
        }
        
        // Find user by user_id or email
        $user = null;
        if ($user_id) {
            $user = User::where('user_id', $user_id)->first();
        }
        if (!$user && $email) {
            // Use case-insensitive email matching (consistent with rest of codebase)
            $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
        }
        
        if (!$user) {
            return response()->json(['past_organizations' => []]);
        }
        
        // Get all organizations the user has been affiliated with
        $pastOrganizations = collect();
        
        // Get primary organization (if exists and not current)
        if ($user->organization_id && $user->organization_id != $currentOrganizationId) {
            $org = $user->organization;
            if ($org) {
                $pastOrganizations->push([
                    'organization_name' => $org->name,
                    'position' => $user->position ?? 'Member',
                    'year' => $this->getAcademicYear($user->created_at),
                    'created_at' => $user->created_at ? $user->created_at->timestamp : 0,
                ]);
            }
        }
        
        // Get additional organizations from pivot table (excluding current organization)
        if (\Illuminate\Support\Facades\Schema::hasTable('organization_user')) {
            $otherOrgs = $user->otherOrganizations()
                ->where('organizations.id', '!=', $currentOrganizationId)
                ->orderBy('organization_user.created_at', 'desc')
                ->get();
            
            foreach ($otherOrgs as $org) {
                $pivot = $org->pivot;
                $createdAt = $pivot->created_at ?? null;
                $pastOrganizations->push([
                    'organization_name' => $org->name,
                    'position' => $pivot->position ?? 'Member',
                    'year' => $this->getAcademicYear($createdAt),
                    'created_at' => $createdAt ? (is_string($createdAt) ? strtotime($createdAt) : $createdAt->timestamp) : 0,
                ]);
            }
        }
        
        // Sort by created_at in reverse chronological order (newest first)
        $pastOrganizations = $pastOrganizations->sortByDesc('created_at')->values();
        
        return response()->json(['past_organizations' => $pastOrganizations]);
    }
    
    /**
     * Get academic year from a date
     */
    private function getAcademicYear($date)
    {
        if (!$date) {
            return date('Y') . '-' . (date('Y') + 1);
        }
        
        $carbonDate = \Carbon\Carbon::parse($date);
        $year = $carbonDate->year;
        
        // Academic year typically runs from August to July
        // If month is August or later, it's the start of the academic year
        if ($carbonDate->month >= 8) {
            return $year . '-' . ($year + 1);
        } else {
            return ($year - 1) . '-' . $year;
        }
    }

    public function store(Request $request)
    {
        // Optional per-staff cap
        $count = User::where('role', 3)->where('supervisor_id', auth()->id())->count();
        if ($count >= 11) {
            return back()->withErrors(['limit' => 'Maximum of 11 student leaders allowed per staff.'])->withInput();
        }

        // Check if user already exists by email or user_id
        $existingUser = User::where('email', $request->email)
            ->orWhere('user_id', $request->user_id)
            ->first();

        // Validation: unique checks only needed if creating new user
        $validationRules = [
            'user_id' => 'required|string|max:50',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'contact_number' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'organization_id' => 'required|exists:organizations,id',
            'position' => 'nullable|string|max:200',
            'image' => 'nullable|image|max:5120',
            'service_order' => 'nullable|file|mimes:pdf,doc,docx|max:15360',
            'length_of_service' => 'nullable|integer|min:0',
            'contract_end_at' => ['nullable','regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'age' => 'nullable|integer|min:1|max:100',
            'civil_status' => 'nullable|in:single,married,divorced,widowed,separated',
            'course_id' => 'nullable|exists:courses,id',
            'year_level' => 'nullable|integer|min:1|max:10',
            'student_type1' => 'nullable|in:regular,irregular,transferee',
            'student_type2' => 'nullable|in:paying,scholar',
            'scholarship_id' => 'nullable|exists:scholarships,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
            'emergency_relation' => 'nullable|string|max:100',
            'complete_home_address' => 'nullable|string|max:500',
            'academic_year' => 'nullable|string|max:20',
            'leadership_org' => 'nullable|array',
            'leadership_position' => 'nullable|array',
            'leadership_year' => 'nullable|array',
        ];

        // Only require password if creating new user
        if (!$existingUser) {
            $validationRules['password'] = 'required|string|min:6|confirmed';
            $validationRules['user_id'] = ['required', 'string', 'max:50', 'unique:users,user_id', new \App\Rules\UserIdByRole(3)];
            $validationRules['email'] = 'required|email|max:255|unique:users,email';
        }

        $data = $request->validate($validationRules);

        if ($existingUser) {
            // Update existing user to be an assistant (if not already role 3)
            $assistant = $existingUser;
            // If user is role 1 (student), allow them to remain role 1 but still be an assistant
            // Only set role to 3 if they're not already a student (role 1)
            if ($assistant->role != 1 && $assistant->role != 3) {
                $assistant->role = 3; // assistant
            }
        } else {
            // Create new user
            $assistant = new User();
            $assistant->user_id = $data['user_id'];
            $assistant->role = 3; // assistant
            $assistant->email_verified_at = now();
        }

        // Update/create assistant fields
        $assistant->first_name = $data['first_name'];
        $assistant->middle_name = $data['middle_name'] ?? null;
        $assistant->last_name = $data['last_name'];
        $assistant->email = $data['email'];
        
        // Only set supervisor_id if not already set, or if this is a new assignment
        // For role 1 users, we allow multiple supervisors
        if (!$existingUser || $assistant->role == 1) {
            // For role 1 users, supervisor_id can vary per assignment
            // We'll store it in assistant_assignments instead
        } else {
        $assistant->supervisor_id = auth()->id();
        }
        
        // Use default password "password" for new users
        $isNewUser = !$existingUser;
        $defaultPassword = 'password';
        if ($isNewUser || !isset($data['password'])) {
            $assistant->password = bcrypt($defaultPassword);
        } else {
            $assistant->password = bcrypt($data['password']);
        }
        
        $assistant->contact_number = $data['contact_number'] ?? ($assistant->contact_number ?? null);
        $assistant->department_id = $data['department_id'] ?? ($assistant->department_id ?? null);
        // Don't set organization_id directly - use assistant_assignments table instead
        // $assistant->organization_id = $data['organization_id'];
        $assistant->position = $data['position'] ?? null;
        
        $assistant->birth_date = $data['birth_date'] ?? ($assistant->birth_date ?? null);
        $assistant->gender = $data['gender'] ?? ($assistant->gender ?? null);
        $assistant->age = $data['age'] ?? ($assistant->age ?? null);
        $assistant->civil_status = $data['civil_status'] ?? ($assistant->civil_status ?? null);
        $assistant->course_id = $data['course_id'] ?? ($assistant->course_id ?? null);
        // Update normalized tables
        if ($data['year_level'] ?? $data['student_type1'] ?? $data['student_type2'] ?? null) {
            $assistant->studentInformation()->updateOrCreate(
                ['user_id' => $assistant->id],
                [
                    'year_level' => $data['year_level'] ?? optional($assistant->studentInformation)->year_level,
                    'student_type1' => $data['student_type1'] ?? optional($assistant->studentInformation)->student_type1,
                    'student_type2' => $data['student_type2'] ?? optional($assistant->studentInformation)->student_type2,
                    'scholarship_id' => $data['scholarship_id'] ?? optional($assistant->studentInformation)->scholarship_id,
                ]
            );
        }
        
        // Update emergency contact
        if ($data['emergency_contact_name'] ?? null) {
            $assistant->emergencyContacts()->delete(); // Remove old
            \App\Models\EmergencyContact::create([
                'user_id' => $assistant->id,
                'name' => $data['emergency_contact_name'],
                'contact_number' => $data['emergency_contact_number'] ?? '',
                'relation' => $data['emergency_relation'] ?? null,
            ]);
        }
        
        // Update address
        if ($data['complete_home_address'] ?? null) {
            $assistant->addresses()->where('type', 'home')->delete(); // Remove old
            \App\Models\Address::create([
                'addressable_type' => \App\Models\User::class,
                'addressable_id' => $assistant->id,
                'type' => 'home',
                'complete_address' => $data['complete_home_address'],
            ]);
        }
        $assistant->academic_year = $data['academic_year'] ?? ($assistant->academic_year ?? null);

        if ($request->hasFile('image')) {
            $assistant->image = $request->file('image')->store('profile_images', 'public');
        }
        if ($request->hasFile('service_order')) {
            $assistant->service_order = $request->file('service_order')->store('service_orders', 'public');
        }

        // Contract end date resolution
        $contractEndAt = null;
        if (!empty($data['contract_end_at'])) {
            // MM/DD/YYYY
            $dt = \DateTime::createFromFormat('m/d/Y', $data['contract_end_at']);
            if ($dt) {
                $contractEndAt = $dt->format('Y-m-d');
            }
        } elseif (!empty($data['length_of_service'])) {
            // Compute from now + length_of_service years
            $contractEndAt = now()->addYears((int)$data['length_of_service'])->toDateString();
        }
        $assistant->length_of_service = $data['length_of_service'] ?? null;
        $assistant->contract_end_at = $contractEndAt;
        $assistant->save();

        // Send credentials email for new users
        if ($isNewUser) {
            try {
                $name = $assistant->first_name . ' ' . $assistant->last_name;
                \Illuminate\Support\Facades\Mail::to($assistant->email)->send(new \App\Mail\AccountCredentialsMail(
                    $assistant->email,
                    $defaultPassword,
                    $name,
                    'Assistant'
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send credentials email to assistant', [
                    'user_id' => $assistant->id,
                    'email' => $assistant->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Check if this specific assignment already exists (same user, organization, position, and supervisor)
        $existingAssignment = \App\Models\AssistantAssignment::where('user_id', $assistant->id)
            ->where('organization_id', $data['organization_id'])
            ->where('position', $data['position'] ?? null)
            ->where('supervisor_id', auth()->id())
            ->first();
        
        if ($existingAssignment) {
            return back()->withErrors(['organization_id' => 'This user is already assigned as an assistant to this organization with the same position.'])->withInput();
        }

        // Create assistant assignment record to track multiple assignments
        \App\Models\AssistantAssignment::create([
            'user_id' => $assistant->id,
            'organization_id' => $data['organization_id'],
            'department_id' => $data['department_id'] ?? null,
            'position' => $data['position'] ?? null,
            'supervisor_id' => auth()->id(),
            'active' => true,
        ]);

        // Save leadership background data
        if (!empty($data['leadership_org'])) {
            $leadershipOrgs = $data['leadership_org'];
            $leadershipPositions = $data['leadership_position'] ?? [];
            $leadershipYears = $data['leadership_year'] ?? [];
            
            foreach ($leadershipOrgs as $index => $org) {
                if (!empty(trim($org))) {
                    \App\Models\AssistantLeadershipBackground::create([
                        'user_id' => $assistant->id,
                        'organization' => trim($org),
                        'position' => !empty($leadershipPositions[$index]) ? trim($leadershipPositions[$index]) : null,
                        'year' => !empty($leadershipYears[$index]) ? trim($leadershipYears[$index]) : null,
                        'order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('staff.student-leaders.index')->with('success', 'Student leader added.');
    }

    public function edit($id)
    {
        // Check if assistant has an assignment with current user as supervisor
        $hasAssignment = \App\Models\AssistantAssignment::where('user_id', $id)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        // Also check legacy supervisor_id for role 3 assistants
        $isLegacySupervisor = User::where('id', $id)
            ->where('role', 3)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        if (!$hasAssignment && !$isLegacySupervisor) {
            abort(404, 'Assistant not found or you do not have permission to edit this assistant.');
        }
        
        $assistant = User::findOrFail($id);
        
        // Load necessary data for the Personal Data Sheet form
        // Use cached reference data
        $departments = \App\Services\CacheService::getDepartments();
        $organizations = \App\Services\CacheService::getOrganizations();
        $courses = \App\Models\Course::orderBy('name')->get();
        
        // Get assistant's current organization (from primary or assignments)
        $currentOrganization = $assistant->organization;
        if (!$currentOrganization) {
            // Check assistant_assignments for organization
            $assignment = \App\Models\AssistantAssignment::where('user_id', $assistant->id)
                ->where('supervisor_id', auth()->id())
                ->where('active', true)
                ->first();
            if ($assignment) {
                $currentOrganization = $assignment->organization;
            }
        }
        
        // Load leadership background
        $leadershipBackgrounds = \App\Models\AssistantLeadershipBackground::where('user_id', $assistant->id)
            ->orderBy('order')
            ->get();
        
        return view('staff.assistants.edit', compact('assistant', 'departments', 'organizations', 'courses', 'currentOrganization', 'leadershipBackgrounds'));
    }

    public function update(Request $request, $id)
    {
        // Check if assistant has an assignment with current user as supervisor
        $hasAssignment = \App\Models\AssistantAssignment::where('user_id', $id)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        // Also check legacy supervisor_id for role 3 assistants
        $isLegacySupervisor = User::where('id', $id)
            ->where('role', 3)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        if (!$hasAssignment && !$isLegacySupervisor) {
            abort(404, 'Assistant not found or you do not have permission to update this assistant.');
        }
        
        $assistant = User::findOrFail($id);

        $data = $request->validate([
            'user_id' => ['required', 'string', 'max:50', 'unique:users,user_id,' . $assistant->id, new \App\Rules\UserIdByRole(3)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $assistant->id,
            'password' => 'nullable|string|min:6|confirmed',
            'contact_number' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'position' => 'nullable|string|max:200',
            'image' => 'nullable|image|mimes:jpeg,jpg,png|max:5120|dimensions:min_width=100,min_height=100',
            'service_order' => 'nullable|file|mimes:pdf,doc,docx|max:15360',
            'length_of_service' => 'nullable|integer|min:0',
            'contract_end_at' => ['nullable','regex:/^\d{2}\/\d{2}\/\d{4}$/'],
            'birth_date' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'age' => 'nullable|integer|min:1|max:100',
            'civil_status' => 'nullable|in:single,married,divorced,widowed,separated',
            'course_id' => 'nullable|exists:courses,id',
            'year_level' => 'nullable|integer|min:1|max:10',
            'student_type1' => 'nullable|in:regular,irregular,transferee',
            'student_type2' => 'nullable|in:paying,scholar',
            'scholarship_id' => 'nullable|exists:scholarships,id',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_number' => 'nullable|string|max:50',
            'emergency_relation' => 'nullable|string|max:100',
            'complete_home_address' => 'nullable|string|max:500',
            'academic_year' => 'nullable|string|max:20',
            'leadership_org' => 'nullable|array',
            'leadership_position' => 'nullable|array',
            'leadership_year' => 'nullable|array',
        ]);

        $assistant->user_id = $data['user_id'];
        $assistant->first_name = $data['first_name'];
        $assistant->middle_name = $data['middle_name'] ?? null;
        $assistant->last_name = $data['last_name'];
        $assistant->email = $data['email'];
        if (!empty($data['password'])) {
            $assistant->password = bcrypt($data['password']);
        }
        $assistant->contact_number = $data['contact_number'] ?? null;
        $assistant->department_id = $data['department_id'] ?? null;
        $assistant->organization_id = $data['organization_id'] ?? $assistant->organization_id; // Use organization from form or keep existing
        $assistant->position = $data['position'] ?? null;
        $assistant->birth_date = $data['birth_date'] ?? null;
        $assistant->gender = $data['gender'] ?? null;
        $assistant->age = $data['age'] ?? null;
        $assistant->civil_status = $data['civil_status'] ?? null;
        $assistant->course_id = $data['course_id'] ?? null;
        $assistant->year_level = $data['year_level'] ?? null;
        $assistant->student_type1 = $data['student_type1'] ?? null;
        $assistant->student_type2 = $data['student_type2'] ?? null;
        $assistant->scholarship_id = $data['scholarship_id'] ?? null;
        $assistant->emergency_contact_name = $data['emergency_contact_name'] ?? null;
        $assistant->emergency_contact_number = $data['emergency_contact_number'] ?? null;
        $assistant->emergency_relation = $data['emergency_relation'] ?? null;
        $assistant->complete_home_address = $data['complete_home_address'] ?? null;
        $assistant->academic_year = $data['academic_year'] ?? null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // Sanitize filename
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $image->getClientOriginalName());
            $assistant->image = $image->storeAs('profile_images', $filename, 'public');
        }
        if ($request->hasFile('service_order')) {
            $serviceOrder = $request->file('service_order');
            // Sanitize filename
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $serviceOrder->getClientOriginalName());
            $assistant->service_order = $serviceOrder->storeAs('service_orders', $filename, 'public');
        }

        $contractEndAt = null;
        if (!empty($data['contract_end_at'])) {
            $dt = \DateTime::createFromFormat('m/d/Y', $data['contract_end_at']);
            if ($dt) {
                $contractEndAt = $dt->format('Y-m-d');
            }
        } elseif (!empty($data['length_of_service'])) {
            $contractEndAt = now()->addYears((int)$data['length_of_service'])->toDateString();
        }
        $assistant->length_of_service = $data['length_of_service'] ?? null;
        $assistant->contract_end_at = $contractEndAt;
        $assistant->save();

        // Update leadership background data (delete existing and recreate)
        \App\Models\AssistantLeadershipBackground::where('user_id', $assistant->id)->delete();
        
        if (!empty($data['leadership_org'])) {
            $leadershipOrgs = $data['leadership_org'];
            $leadershipPositions = $data['leadership_position'] ?? [];
            $leadershipYears = $data['leadership_year'] ?? [];
            
            foreach ($leadershipOrgs as $index => $org) {
                if (!empty(trim($org))) {
                    \App\Models\AssistantLeadershipBackground::create([
                        'user_id' => $assistant->id,
                        'organization' => trim($org),
                        'position' => !empty($leadershipPositions[$index]) ? trim($leadershipPositions[$index]) : null,
                        'year' => !empty($leadershipYears[$index]) ? trim($leadershipYears[$index]) : null,
                        'order' => $index,
                    ]);
                }
            }
        }

        return redirect()->route('staff.student-leaders.index')->with('success', 'Student leader updated.');
    }

    public function destroy($id)
    {
        // Check if assistant has an assignment with current user as supervisor
        $hasAssignment = \App\Models\AssistantAssignment::where('user_id', $id)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        // Also check legacy supervisor_id for role 3 assistants
        $isLegacySupervisor = User::where('id', $id)
            ->where('role', 3)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        if (!$hasAssignment && !$isLegacySupervisor) {
            abort(404, 'Assistant not found or you do not have permission to delete this assistant.');
        }
        
        $assistant = User::findOrFail($id);
        
        // Use database transaction to ensure all deletions happen atomically
        DB::transaction(function () use ($assistant) {
            // Temporarily disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            try {
                // Directly delete from pivot table to avoid foreign key constraint issues
                DB::table('organization_user')
                    ->where('user_id', $assistant->id)
                    ->delete();
                
                // Delete leadership backgrounds
                DB::table('assistant_leadership_backgrounds')
                    ->where('user_id', $assistant->id)
                    ->delete();
                
                // Delete assistant assignments
                DB::table('student_leaders_information')
                    ->where('user_id', $assistant->id)
                    ->delete();
                
                // Now delete the user
        $assistant->delete();
            } finally {
                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        });
        
        return redirect()->route('staff.student-leaders.index')->with('success', 'Student leader deleted.');
    }

    public function suspend($id)
    {
        // Check if assistant has an assignment with current user as supervisor
        $hasAssignment = \App\Models\AssistantAssignment::where('user_id', $id)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        // Also check legacy supervisor_id for role 3 assistants
        $isLegacySupervisor = User::where('id', $id)
            ->where('role', 3)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        if (!$hasAssignment && !$isLegacySupervisor) {
            abort(404, 'Assistant not found or you do not have permission to suspend this assistant.');
        }
        
        $assistant = User::findOrFail($id);
        $assistant->suspended = true;
        $assistant->save();
        return redirect()->route('staff.assistants.index')->with('success', 'Assistant suspended.');
    }

    public function resume($id)
    {
        // Check if assistant has an assignment with current user as supervisor
        $hasAssignment = \App\Models\AssistantAssignment::where('user_id', $id)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        // Also check legacy supervisor_id for role 3 assistants
        $isLegacySupervisor = User::where('id', $id)
            ->where('role', 3)
            ->where('supervisor_id', auth()->id())
            ->exists();
        
        if (!$hasAssignment && !$isLegacySupervisor) {
            abort(404, 'Assistant not found or you do not have permission to resume this assistant.');
        }
        
        $assistant = User::findOrFail($id);
        $assistant->suspended = false;
        $assistant->save();
        return redirect()->route('staff.assistants.index')->with('success', 'Assistant resumed.');
    }

    // Show all organizations for the current staff with assistant management
    public function organizations()
    {
        $user = auth()->user();
        
        // Get staff record by email
        $staff = \App\Models\Staff::where('email', $user->email)->first();
        
        $organizations = collect();
        
        if ($staff) {
            // Get single organization
            if ($staff->organization_id) {
                $org = \App\Models\Organization::with('department')->find($staff->organization_id);
                if ($org) {
                    $organizations->push($org);
                }
            }
            
            // Get additional organizations from many-to-many relationship
            $additionalOrgs = $staff->organizations()->with('department')->get();
            foreach ($additionalOrgs as $org) {
                // Avoid duplicates
                if (!$organizations->contains('id', $org->id)) {
                    $organizations->push($org);
                }
            }
        }
        
        // Also check if user has organizations directly (for staff who are users)
        if ($user->organization_id) {
            $org = \App\Models\Organization::with('department')->find($user->organization_id);
            if ($org && !$organizations->contains('id', $org->id)) {
                $organizations->push($org);
            }
        }
        
        // Get additional organizations from organization_user table
        if (method_exists($user, 'otherOrganizations')) {
            $userOrgs = $user->otherOrganizations()->with('department')->get();
            foreach ($userOrgs as $org) {
                if (!$organizations->contains('id', $org->id)) {
                    $organizations->push($org);
                }
            }
        }
        
        // For each organization, fetch membership statistics and events
        $organizationsWithStats = $organizations->map(function ($org) {
            // Refresh organization to ensure department_id is current
            $org->refresh();
            
            // Get all students (role = 1) who belong to this organization
            // For department-related organizations (academic), automatically include all students from that department
            // Match using department_id from the users table with organization's department_id from the organizations table
            
            $orgDepartmentId = $org->department_id;
            
            if ($orgDepartmentId) {
                // If organization is department-related (academic), include ALL students from that department
                // Match students' department_id (from users table) with organization's department_id (from organizations table)
                // This makes students under Information Technology automatically belong to Student Council of Information Technology
                $members = \App\Models\User::where('role', 1)
                    ->whereNotNull('department_id')
                    ->where('department_id', $orgDepartmentId)
                    ->get();
            } else {
                // For non-academic organizations (no department_id), only count explicit assignments
                $members = \App\Models\User::where('role', 1)
                    ->where(function ($query) use ($org) {
                        // Direct organization assignment
                        $query->where('organization_id', $org->id)
                            // Organization via pivot table
                            ->orWhereHas('otherOrganizations', function ($q) use ($org) {
                                $q->where('organizations.id', $org->id);
                            });
                    })
                    ->get();
            }
            
            // Calculate total members
            $totalMembers = $members->count();
            
            // Calculate by gender
            $maleCount = $members->where('gender', 'male')->count();
            $femaleCount = $members->where('gender', 'female')->count();
            $otherCount = $members->where('gender', 'other')->count();
            
            // Calculate by year level (1st to 5th year)
            $yearLevelCounts = [];
            for ($year = 1; $year <= 5; $year++) {
                $yearLevelCounts[$year] = [
                    'total' => $members->filter(function($member) use ($year) {
                        return optional($member->studentInformation)->year_level == $year;
                    })->count(),
                    'male' => $members->filter(function($member) use ($year) {
                        return $member->gender == 'male' && optional($member->studentInformation)->year_level == $year;
                    })->count(),
                    'female' => $members->filter(function($member) use ($year) {
                        return $member->gender == 'female' && optional($member->studentInformation)->year_level == $year;
                    })->count(),
                    'other' => $members->filter(function($member) use ($year) {
                        return $member->gender == 'other' && optional($member->studentInformation)->year_level == $year;
                    })->count(),
                ];
            }
            
            // Get events for this organization
            // Events created by the organization
            $organizationEvents = \App\Models\Event::where('organization_id', $org->id)
                ->with('creator', 'organization')
                ->orderBy('event_date', 'desc')
                ->get();
            
            // Pending events for this organization
            $pendingEvents = \App\Models\Event::where('organization_id', $org->id)
                ->where('status', 'pending')
                ->with('creator', 'organization')
                ->orderBy('event_date', 'desc')
                ->get();
            
            // Events history (approved and past events, or declined events)
            $eventsHistory = \App\Models\Event::where('organization_id', $org->id)
                ->whereIn('status', ['approved', 'declined'])
                ->with('creator', 'organization')
                ->orderBy('event_date', 'desc')
                ->get();
            
            return [
                'organization' => $org,
                'total_members' => $totalMembers,
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'other_count' => $otherCount,
                'year_level_counts' => $yearLevelCounts,
                'organization_events' => $organizationEvents,
                'pending_events' => $pendingEvents,
                'events_history' => $eventsHistory,
            ];
        });
        
        // Get all approved events with Required Student Participation ON for QR scanner dropdown
        $events = \App\Models\Event::where('status', 'approved')
            ->where('required_student_participation', true)
            ->orderBy('event_date', 'desc')
            ->get();
        
        return view('staff.organizations.index', compact('organizationsWithStats', 'organizations', 'events'));
    }

    // List assistants for a specific organization
    public function organizationAssistants($organizationId)
    {
        $organization = \App\Models\Organization::findOrFail($organizationId);
        
        // Verify the user has access to this organization
        $user = auth()->user();
        $staff = \App\Models\Staff::where('email', $user->email)->first();
        
        $hasAccess = false;
        
        if ($staff) {
            $hasAccess = ($staff->organization_id == $organizationId) || 
                         $staff->organizations()->where('organizations.id', $organizationId)->exists();
        }
        
        if (!$hasAccess && $user->organization_id == $organizationId) {
            $hasAccess = true;
        }
        
        if (!$hasAccess && method_exists($user, 'otherOrganizations')) {
            $hasAccess = $user->otherOrganizations()->where('organizations.id', $organizationId)->exists();
        }
        
        if (!$hasAccess) {
            abort(403, 'You do not have access to this organization.');
        }
        
        // Get assistants for this organization
        $assistants = User::where('role', 3)
            ->where('supervisor_id', auth()->id())
            ->where(function($q) use ($organizationId) {
                $q->where('organization_id', $organizationId)
                  ->orWhereHas('otherOrganizations', function($oq) use ($organizationId) {
                      $oq->where('organizations.id', $organizationId);
                  });
            })
            ->orderBy('first_name')
            ->get();
        
        return view('staff.organizations.assistants', compact('organization', 'assistants'));
    }
}
