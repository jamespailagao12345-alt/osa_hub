<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Staff;
use App\Models\User;
use App\Models\Department;
use App\Models\Organization;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountCredentialsMail;

class StaffController extends Controller
{
    /**
     * Generate a unique 7-digit staff_id
     */
    private function generateStaffId(): string
    {
        do {
            $staffId = str_pad((string) mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
            // Check if it exists in users table
            $existsInUsers = \App\Models\User::where('user_id', $staffId)->exists();
            // Check if it exists in staff_information table
            $existsInStaff = Staff::where('staff_id', $staffId)->exists();
        } while ($existsInUsers || $existsInStaff);
        
        return $staffId;
    }
    
    public function updateEmployeeId(Request $request, $id)
    {
        $request->validate([
            'staff_id' => 'required|string|unique:staff_information,staff_id,' . $id . '|unique:users,user_id'
        ]);
        $staff = Staff::findOrFail($id);
        $staff->staff_id = $request->staff_id;
        $staff->save();
        
        // Sync staff_id to the users table (as user_id)
        $user = User::where('email', $staff->email)->first();
        if ($user) {
            $user->user_id = $request->staff_id;
            $user->save();
        }
        
        return back()->with('success', 'Staff ID updated successfully!');
    }
    public function profile()
    {
        $user = auth()->user()->load(['department', 'course', 'organization', 'otherOrganizations']);
        $staff = null;
        
        // Get Staff record by email if user has email
        if ($user && $user->email) {
            $staff = Staff::where('email', $user->email)
                ->with(['department', 'organization', 'organizations'])
                ->first();
        }
        
        // If staff not found, use user data
        if (!$staff) {
            $staff = $user;
        }
        
        return view('staff.profile', compact('staff', 'user'));
    }

    public function updateAboutMe(Request $request)
    {
        $request->validate([
            'about_me' => 'nullable|string|max:5000',
        ]);

        $user = auth()->user();
        $staff = null;
        
        // Get Staff record by email if user has email
        if ($user && $user->email) {
            $staff = Staff::where('email', $user->email)->first();
        }
        
        if ($staff) {
            $staff->about_me = $request->about_me;
            $staff->save();
        } else {
            // If no staff record, update user record
            $user->about_me = $request->about_me;
            $user->save();
        }
        
        return back()->with('success', 'About Me information updated successfully.');
    }
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:staff_information',
            'password' => 'required|min:8|confirmed',
            'department_id' => 'nullable|integer',
            'designation' => 'required|string',
            'organization_ids' => 'array',
            'organization_ids.*' => 'integer|exists:organizations,id',
            'contact_number' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'service_order' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'birth_date' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => 'nullable|string',
            'age' => 'nullable|integer',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'contract_start_at' => 'nullable|date',
            'contract_end_at' => 'nullable|date',
        ]);

        // Generate unique 7-digit staff_id
        $staffId = $this->generateStaffId();
        
        // Initialize data array with all fields, ensuring null values are included
        $data = [
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'middle_name' => $request->input('middle_name', null),
            'email' => $request->input('email'),
            'designation' => $request->input('designation'),
            'department_id' => $request->input('department_id', null),
            'contact_number' => $request->input('contact_number', null),
            'birth_date' => $request->input('birth_date', null),
            'gender' => $request->input('gender', null),
            'age' => $request->input('age', null),
            'length_of_service' => $request->input('length_of_service', null),
            'contract_start_at' => null,
            'contract_end_at' => null,
            'image' => null,
            'service_order' => null,
            'employment_status' => 'active', // Default value as per migration
            'about_me' => null,
        ];
        
        // Use the password provided by admin when creating staff account
        $adminProvidedPassword = $request->input('password');
        $data['password'] = bcrypt($adminProvidedPassword);
        
        // Auto-calc age from birth_date if provided
        if (!empty($data['birth_date'])) {
            try {
                $birthDate = \Carbon\Carbon::parse($data['birth_date']);
                // Validate that the year is reasonable (between 1900 and current year)
                $currentYear = now()->year;
                if ($birthDate->year < 1900 || $birthDate->year > $currentYear) {
                    throw new \Exception('Invalid birth year');
                }
                // Ensure the date is in the past
                if ($birthDate->isFuture()) {
                    throw new \Exception('Birth date cannot be in the future');
                }
                $data['birth_date'] = $birthDate->format('Y-m-d');
                $data['age'] = $birthDate->age;
            } catch (\Exception $e) {
                // If date parsing fails, set to null to prevent invalid data
                $data['birth_date'] = null;
                Log::warning('Invalid birth_date provided: ' . ($request->input('birth_date') ?? 'null'), [
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        $data['admin_id'] = auth()->id();
        
        // Handle image upload - set default image if no image is uploaded
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('staff_images', 'public');
        } else {
            // Set default profile image path (relative to storage/app/public)
            $data['image'] = 'defaults/default-profile.png';
        }
        
        // Handle service order upload
        if ($request->hasFile('service_order')) {
            $data['service_order'] = $request->file('service_order')->store('service_orders', 'public');
        }
        
        // Store length of service if provided (numeric from add form)
        if ($request->filled('length_of_service')) {
            $data['length_of_service'] = (string) $request->input('length_of_service');
        }
        
        // Handle Contract Start Date (always set, even if null)
        if ($request->filled('contract_start_at')) {
            try {
                $dt = \Carbon\Carbon::parse($request->input('contract_start_at'));
                $data['contract_start_at'] = $dt->startOfDay()->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // keep as null if parsing fails
                $data['contract_start_at'] = null;
            }
        }
        
        // Handle Effectivity Date (Contract End Date) - always set, even if null
        if ($request->filled('contract_end_at')) {
            try {
                $dt = \Carbon\Carbon::parse($request->input('contract_end_at'));
                $data['contract_end_at'] = $dt->endOfDay()->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // keep as null if parsing fails
                $data['contract_end_at'] = null;
            }
        }
        // Determine primary organization_id for User (first organization from organization_ids if available)
        $primaryOrganizationId = null;
        if ($request->has('organization_ids') && !empty($request->organization_ids)) {
            $orgIds = $request->organization_ids;
            $primaryOrganizationId = is_array($orgIds) && !empty($orgIds) ? reset($orgIds) : null;
        }

        // Create or update matching User record FIRST (before creating Staff)
        // The staff_information.user_id foreign key references users.id (auto-increment)
        // Check by email first (primary identifier)
        $user = \App\Models\User::where('email', $data['email'])->first();
        
        $isNewUser = false;
        if ($user) {
            // Update existing user
            $updateData = [
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'], // already hashed
                'role' => 2,
                'department_id' => $data['department_id'] ?? null,
                'organization_id' => $primaryOrganizationId,
                'contact_number' => $data['contact_number'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
                'image' => $data['image'] ?? null,
                'user_id' => $staffId, // Set the generated user_id
                'email_verified_at' => $user->email_verified_at ?? now(),
            ];
            
            $user->update($updateData);
        } else {
            // Create new user
            $isNewUser = true;
            $user = \App\Models\User::create([
                'user_id' => $staffId, // Set the generated user_id
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'], // already hashed
                'role' => 2,
                'department_id' => $data['department_id'] ?? null,
                'organization_id' => $primaryOrganizationId,
                'contact_number' => $data['contact_number'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
                'image' => $data['image'] ?? null,
                'email_verified_at' => now(),
            ]);
        }

        // Now create Staff record with the User's id (auto-increment primary key) for the foreign key
        // Set staff_id to the generated 7-digit ID
        $data['staff_id'] = $staffId;
        // Set user_id to the User's auto-increment id (for the foreign key constraint)
        $data['user_id'] = $user->id;
        
        // Ensure all fillable fields are present in $data (set to null if not provided)
        // This ensures the record is always created, even with null values
        $fillableFields = [
            'first_name', 'last_name', 'middle_name', 'user_id', 'staff_id', 'email',
            'password', 'designation', 'department_id', 'organization_id', 'admin_id',
            'contact_number', 'image', 'service_order', 'birth_date', 'gender', 'age',
            'length_of_service', 'contract_start_at', 'contract_end_at', 'about_me'
        ];
        
        foreach ($fillableFields as $field) {
            if (!array_key_exists($field, $data)) {
                $data[$field] = null;
            }
        }
        
        // Ensure employment_status has a default value (cannot be null per database constraint)
        if (!isset($data['employment_status']) || $data['employment_status'] === null) {
            $data['employment_status'] = 'active';
        }
        
        // Create staff record - this will always succeed even with null values
        try {
            $staff = Staff::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create staff record', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
        
        // Attach organizations
        if ($request->has('organization_ids')) {
            $staff->organizations()->sync($request->organization_ids);
        }

        // Send credentials email for new users
        if ($isNewUser) {
            try {
                $name = $user->first_name . ' ' . $user->last_name;
                // Send email with the password provided by admin (not hashed)
                Mail::to($user->email)->send(new AccountCredentialsMail(
                    $user->email,  // Username is the email address
                    $adminProvidedPassword,  // Password provided by admin
                    $name,
                    'Staff'
                ));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send credentials email to staff', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Staff member added successfully!');
    }


    public function edit($id)
    {
        $staff = Staff::findOrFail($id);
        $departments = \App\Services\CacheService::getDepartments();
        
        // Pre-populate organizations: if staff has a department, show that department's orgs + unassigned; else show unassigned + current org
        if ($staff->department_id) {
            $organizations = \App\Services\CacheService::getOrganizationsByDepartment($staff->department_id)
                ->merge(\App\Services\CacheService::getOrganizationsByDepartment(null))
                ->unique('id')
                ->sortBy('name')
                ->values();
        } else {
            $organizations = \App\Services\CacheService::getOrganizationsByDepartment(null);
            
            // If staff has an organization_id, add that organization if it's not already in the collection
            if ($staff->organization_id) {
                $currentOrg = \App\Models\Organization::find($staff->organization_id);
                if ($currentOrg && !$organizations->contains('id', $staff->organization_id)) {
                    $organizations->push($currentOrg);
                }
            }
            
            $organizations = $organizations->sortBy('name')->values();
        }
        
        // Get organization IDs that are already assigned to other staff members
        $assignedOrgIds = DB::table('organization_staff')
            ->where('staff_id', '!=', $id)
            ->pluck('organization_id')
            ->toArray();
        
        return view('admin.edit-staff', compact('staff', 'departments', 'organizations', 'assignedOrgIds'));
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        
        // Validate required fields first
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:staff_information,email,' . $id,
            'user_id' => ['required', 'string', 'max:255', 'unique:staff_information,user_id,' . $id, new \App\Rules\UserIdByRole(2)],
            'designation' => 'required|string|max:255',
        ]);
        
        // Optional admin-set password validation
        if ($request->filled('new_password')) {
            $request->validate([
                'new_password' => 'required|string|min:8|confirmed',
            ]);
        }
        
        $data = $request->only([
            'first_name', 'last_name', 'middle_name', 'user_id', 'email', 'designation', 'department_id', 'contact_number', 'birth_date', 'gender', 'age', 'employment_status'
        ]);
        
        // Sync organizations
        if ($request->has('organization_ids')) {
            $staff->organizations()->sync($request->organization_ids);
        }
        
        // Determine primary organization_id for User (first organization from organization_ids if available)
        $primaryOrganizationId = null;
        if ($request->has('organization_ids') && !empty($request->organization_ids)) {
            $orgIds = $request->organization_ids;
            $primaryOrganizationId = is_array($orgIds) && !empty($orgIds) ? reset($orgIds) : null;
        }
        
        // Save human-friendly LOS string if provided
        $losRaw = trim((string) $request->input('length_of_service', ''));
        $data['length_of_service'] = ($losRaw === '') ? null : $losRaw;

        // Auto-calc age from birth_date if provided
        if (!empty($data['birth_date'])) {
            try {
                $data['age'] = \Carbon\Carbon::parse($data['birth_date'])->age;
            } catch (\Exception $e) {
                // ignore
            }
        }

        // Handle Contract Start Date
        if ($request->filled('contract_start_at')) {
            try {
                $dt = \Carbon\Carbon::parse($request->input('contract_start_at'));
                $data['contract_start_at'] = $dt->startOfDay()->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // keep as given if parsing fails
                $data['contract_start_at'] = $request->input('contract_start_at');
            }
        }

        // Prefer explicit Effectivity Date (Contract End Date) if provided; otherwise compute from LOS only when units exist
        $contractEndRaw = trim((string) $request->input('contract_end_at', ''));
        $losInput = trim((string) $request->input('length_of_service', ''));

        if ($contractEndRaw !== '') {
            try {
                // Parse date input (Y-m-d format) or MM/DD/YYYY format
                try {
                    $dt = \Carbon\Carbon::createFromFormat('m/d/Y', $contractEndRaw)->endOfDay();
                } catch (\Exception $e) {
                    // Fallback: attempt a generic parse (for Y-m-d format from date input)
                    $dt = \Carbon\Carbon::parse($contractEndRaw)->endOfDay();
                }
                $data['contract_end_at'] = $dt->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // As a last resort, store the raw string
                $data['contract_end_at'] = $contractEndRaw;
            }
        } elseif ($losInput !== '') {
            // Compute only if LOS has units (e.g., 1y, 6mo, 2w, 3d, 5h, 30m)
            preg_match_all('/(\d+)\s*(y|yr|yrs|year|years|mo|month|months|w|wk|wks|week|weeks|d|day|days|h|hr|hrs|hour|hours|m|min|mins|minute|minutes)\b/i', $losInput, $matches, PREG_SET_ORDER);
            if (!empty($matches)) {
                $dt = now();
                foreach ($matches as $m) {
                    $val = (int) $m[1];
                    $unit = strtolower($m[2]);
                    switch ($unit) {
                        case 'y': case 'yr': case 'yrs': case 'year': case 'years':
                            $dt = $dt->addYears($val); break;
                        case 'mo': case 'month': case 'months':
                            $dt = $dt->addMonths($val); break;
                        case 'w': case 'wk': case 'wks': case 'week': case 'weeks':
                            $dt = $dt->addWeeks($val); break;
                        case 'd': case 'day': case 'days':
                            $dt = $dt->addDays($val); break;
                        case 'h': case 'hr': case 'hrs': case 'hour': case 'hours':
                            $dt = $dt->addHours($val); break;
                        case 'm': case 'min': case 'mins': case 'minute': case 'minutes':
                            $dt = $dt->addMinutes($val); break;
                    }
                }
                $data['contract_end_at'] = $dt->format('Y-m-d H:i:s');
            }
        }

        // Handle image and service order uploads with improved validation
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,jpg,png|max:2048|dimensions:min_width=100,min_height=100',
            ]);
            $image = $request->file('image');
            // Sanitize filename
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $image->getClientOriginalName());
            $data['image'] = $image->storeAs('staff_images', $filename, 'public');
        }
        if ($request->hasFile('service_order')) {
            $request->validate([
                'service_order' => 'file|mimes:pdf,doc,docx|max:10240',
            ]);
            $serviceOrder = $request->file('service_order');
            // Sanitize filename
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $serviceOrder->getClientOriginalName());
            $data['service_order'] = $serviceOrder->storeAs('service_orders', $filename, 'public');
        }

        try {
            // Get the user BEFORE updating staff (in case email changes)
            $oldEmail = $staff->email;
            $newEmail = $data['email'] ?? $oldEmail;
            
            // Try to find user by multiple methods (case-insensitive)
            $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($oldEmail))])->first();
            
            // If not found by old email, try new email (case-insensitive)
            if (!$user && $newEmail !== $oldEmail) {
                $user = User::whereRaw('LOWER(email) = ?', [strtolower(trim($newEmail))])->first();
            }
            
            // If still not found, try by user_id
            if (!$user && !empty($data['user_id'])) {
                $user = User::where('user_id', $data['user_id'])->first();
            }
            
            // Update staff first
            $staff->update($data);
            
            // Refresh staff to get updated values
            $staff->refresh();
            
            // If email changed, update ALL staff records with the old email to the new email
            if ($newEmail !== $oldEmail && $oldEmail) {
                Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($oldEmail))])
                    ->where('id', '!=', $staff->id)
                    ->update(['email' => $newEmail]);
            }
            
            // Sync all changes to the User table
            if ($user) {
                // Update all user fields that match staff fields
                // Use data from request if available, otherwise use updated staff values
                $user->first_name = $data['first_name'] ?? $staff->first_name;
                $user->last_name = $data['last_name'] ?? $staff->last_name;
                $user->middle_name = $data['middle_name'] ?? $staff->middle_name ?? '';
                $user->user_id = $data['user_id'] ?? $staff->user_id;
                $user->email = $data['email'] ?? $staff->email; // Update email if changed
                $user->contact_number = $data['contact_number'] ?? $staff->contact_number ?? '';
                $user->birth_date = $data['birth_date'] ?? $staff->birth_date;
                $user->gender = $data['gender'] ?? $staff->gender;
                $user->age = $data['age'] ?? $staff->age;
                $user->department_id = $data['department_id'] ?? $staff->department_id;
                $user->organization_id = $primaryOrganizationId ?? $user->organization_id; // Update if new orgs provided, otherwise keep existing
                
                // Update image and service_order if they exist in data
                if (isset($data['image'])) {
                    $user->image = $data['image'];
                }
                if (isset($data['service_order'])) {
                    $user->service_order = $data['service_order'];
                }
                
                // Update designation if it exists in User model
                if (isset($data['designation'])) {
                    $user->designation = $data['designation'];
                } elseif ($staff->designation) {
                    $user->designation = $staff->designation;
                }
                
                // Handle employment status
                $employmentStatus = $data['employment_status'] ?? $staff->employment_status;
                if (in_array($employmentStatus, ['ended','inactive'])) {
                    // Block access without mutating the user's role to avoid DB NOT NULL constraint issues
                    // Optionally, we could set a separate flag on users if available.
                } elseif ($employmentStatus === 'active' && $user->role !== 2) {
                    // Reactivate as Staff
                    $user->role = 2;
                    if (is_null($user->email_verified_at)) {
                        $user->email_verified_at = now();
                    }
                }
                
                // Update password if provided
                if ($request->filled('new_password')) {
                    $user->password = bcrypt($request->input('new_password'));
                }
                
                // Save user changes
                try {
                    $user->save();
                } catch (\Exception $userException) {
                    \Illuminate\Support\Facades\Log::warning('User update failed but staff was updated', [
                        'staff_id' => $staff->id,
                        'user_id' => $user->id ?? null,
                        'error' => $userException->getMessage(),
                    ]);
                    // Continue - staff update was successful
                }
            } else {
                // If user not found by email, try to find by user_id
                if (!empty($data['user_id'])) {
                    $user = User::where('user_id', $data['user_id'])->first();
                    if ($user) {
                        // Update user with staff data
                        $user->first_name = $data['first_name'] ?? $staff->first_name;
                        $user->last_name = $data['last_name'] ?? $staff->last_name;
                        $user->middle_name = $data['middle_name'] ?? $staff->middle_name ?? '';
                        $user->email = $data['email'] ?? $staff->email;
                        $user->contact_number = $data['contact_number'] ?? $staff->contact_number ?? '';
                        $user->birth_date = $data['birth_date'] ?? $staff->birth_date;
                        $user->gender = $data['gender'] ?? $staff->gender;
                        $user->age = $data['age'] ?? $staff->age;
                        $user->department_id = $data['department_id'] ?? $staff->department_id;
                        $user->organization_id = $primaryOrganizationId ?? $user->organization_id; // Update if new orgs provided, otherwise keep existing
                        if (isset($data['designation'])) {
                            $user->designation = $data['designation'];
                        } elseif ($staff->designation) {
                            $user->designation = $staff->designation;
                        }
                        if (isset($data['image'])) {
                            $user->image = $data['image'];
                        }
                        if (isset($data['service_order'])) {
                            $user->service_order = $data['service_order'];
                        }
                        try {
                            $user->save();
                        } catch (\Exception $userException) {
                            \Illuminate\Support\Facades\Log::warning('User update failed but staff was updated', [
                                'staff_id' => $staff->id,
                                'user_id' => $user->id ?? null,
                                'error' => $userException->getMessage(),
                            ]);
                            // Continue - staff update was successful
                        }
                    }
                }
            }

            \Illuminate\Support\Facades\Log::info('Staff updated successfully', [
                'staff_id' => $staff->id,
                'user_id' => $user->id ?? null,
                'updated_by' => auth()->id(),
                'timestamp' => now(),
            ]);

            // Redirect back to edit page with success message if staff update succeeded
            // Even if user update failed, staff update was successful
            return redirect()->route('admin.staff.edit', $staff->id)
                ->with('success', 'Staff details updated successfully!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update staff', [
                'staff_id' => $staff->id,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update staff: ' . $e->getMessage());
        }
    }

    public function addAssistant(Request $request)
    {
        $count = User::where('role', 3)->count();
        if ($count >= 11) {
            return back()->withErrors(['limit' => 'Maximum of 11 student leaders allowed.']);
        }
        $request->validate([
            'user_id' => 'required|unique:users',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);
        User::create([
            'user_id' => $request->user_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 3,
            'email_verified_at' => now(),
        ]);
        return back()->with('success', 'Student leader added.');
    }

    public function listAssistants()
    {
        $assistants = User::where('role', 3)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        return view('admin.assistants', compact('assistants'));
    }

    public function editAssistant($id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);
        return view('admin.assistants.edit', compact('assistant'));
    }

    public function updateAssistant(Request $request, $id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);
        $request->validate([
            'user_id' => ['required', 'unique:users,user_id,' . $assistant->id, new \App\Rules\UserIdByRole(3)],
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $assistant->id,
            'password' => 'nullable|min:8',
        ]);
        $assistant->user_id = $request->user_id;
        $assistant->first_name = $request->first_name;
        $assistant->last_name = $request->last_name;
        $assistant->email = $request->email;
        if ($request->filled('password')) {
            $assistant->password = bcrypt($request->password);
        }
        $assistant->save();
        return redirect()->route('admin.assistants.index')->with('success', 'Assistant updated.');
    }

    public function destroyAssistant($id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);
        $assistant->delete();
        return back()->with('success', 'Assistant deleted.');
    }

    public function destroy($id)
    {
        // Find the Staff record by ID
        $staff = Staff::findOrFail($id);
        
        // Find and delete the corresponding User record
        $user = User::where('email', $staff->email)->first();
        if ($user) {
            $user->delete();
        }
        
        // Delete the Staff record
        $staff->delete();
        
        // Redirect to staff list page instead of back() to avoid 404 errors
        return redirect()->route('admin.show-staff')->with('success', 'Staff member deleted successfully!');
    }

    /**
     * Resend verification email to staff
     */
    public function resendVerificationEmail($id)
    {
        try {
            $staff = Staff::with('user')->findOrFail($id);
            $user = $staff->user;
            
            // If user relationship doesn't exist, try to find by email
            if (!$user && $staff->email) {
                $user = User::where('email', $staff->email)->first();
            }
            
            if (!$user) {
                return redirect()->back()->with('error', 'User record not found for this staff member. Please ensure the staff member has a corresponding user account.');
            }
            
            // Validate email exists
            if (empty($user->email)) {
                return redirect()->back()->with('error', 'Staff email address is missing. Cannot send verification email.');
            }
            
            // Get the current password from staff record (it's hashed, so we need to generate a new temporary one)
            // Or use the password from the staff record if available
            // For staff, we'll generate a temporary password: last_name@staff_id (lowercase)
            $lastName = $staff->last_name ?? $user->last_name ?? 'staff';
            $staffId = $staff->staff_id ?? $staff->user_id ?? $user->user_id ?? $user->id;
            $tempPassword = strtolower($lastName) . '@' . $staffId;
            
            // Update password
            $user->password = bcrypt($tempPassword);
            
            // Increment verification email count
            $user->verification_email_count = ($user->verification_email_count ?? 0) + 1;
            $user->save();
            
            // Send verification email using AccountCredentialsMail
            try {
                $name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if (empty($name)) {
                    $name = 'Staff Member';
                }
                
                Mail::to($user->email)->send(new AccountCredentialsMail(
                    $user->email,  // Username is the email address
                    $tempPassword,  // Temporary password
                    $name,
                    'Staff'
                ));
                
                \Illuminate\Support\Facades\Log::info('Verification email resent to staff', [
                    'staff_id' => $staff->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'verification_email_count' => $user->verification_email_count,
                    'sent_by' => auth()->id(),
                    'timestamp' => now(),
                ]);
                
                return redirect()->back()->with('success', "Verification email sent successfully. (Sent {$user->verification_email_count} time(s) to this email address)");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send verification email to staff', [
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
            \Illuminate\Support\Facades\Log::error('Failed to resend verification email to staff', [
                'staff_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->back()->with('error', 'Failed to resend verification email. Please try again.');
        }
    }
}