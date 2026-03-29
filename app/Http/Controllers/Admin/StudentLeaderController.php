<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentLeaderController extends Controller
{
    // List all student leaders (role 3)
    public function index()
    {
        $studentLeaders = User::where('role', 3)
            ->with(['department', 'organization', 'otherOrganizations', 'supervisor'])
            ->orderBy('last_name')
            ->get();
        return view('admin.student-leaders.index', compact('studentLeaders'));
    }

    public function create()
    {
        $organizations = \App\Models\Organization::orderBy('name')->get();
        return view('admin.student-leaders.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'string', 'max:50', 'unique:users', new \App\Rules\UserIdByRole(3)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users',
            'contact_number' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_ids' => 'nullable|array|max:5',
            'organization_ids.*' => 'integer|exists:organizations,id',
        ]);

        // Validate: Student leader must belong to at least one organization
        $allOrgIds = collect($data['organization_ids'] ?? []);
        if (!empty($data['organization_id'])) {
            $allOrgIds->push($data['organization_id']);
        }
        $allOrgIds = $allOrgIds->unique()->values();
        
        if ($allOrgIds->isEmpty()) {
            return back()->withErrors(['organization_ids' => 'Student leader must belong to at least one organization.'])->withInput();
        }
        
        if ($allOrgIds->count() > 5) {
            return back()->withErrors(['organization_ids' => 'Student leader can belong to a maximum of 5 organizations.'])->withInput();
        }

        // Validate: Each organization can have maximum 20 student leaders
        foreach ($allOrgIds as $orgId) {
            $currentCount = User::where('role', 3)
                ->where(function($q) use ($orgId) {
                    $q->where('organization_id', $orgId)
                      ->orWhereHas('otherOrganizations', function($oq) use ($orgId) {
                          $oq->where('organizations.id', $orgId);
                      });
                })
                ->count();
            
            if ($currentCount >= 20) {
                $org = \App\Models\Organization::find($orgId);
                return back()->withErrors(['organization_ids' => "Organization '{$org->name}' already has the maximum of 20 student leaders."])->withInput();
            }
        }

        // Use default password "password"
        $defaultPassword = 'password';
        
        $studentLeader = User::create([
            'user_id' => $data['user_id'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'] ?? null,
            'password' => bcrypt($defaultPassword),
            'role' => 3,
            'organization_id' => $data['organization_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        // Sync other organizations (many-to-many)
        // Remove the primary organization from the list to avoid duplication
        $otherOrgIds = $allOrgIds->reject(function($orgId) use ($data) {
            return $orgId == ($data['organization_id'] ?? null);
        })->toArray();
        
        if (\Illuminate\Support\Facades\Schema::hasTable('organization_user') && !empty($otherOrgIds)) {
            $studentLeader->otherOrganizations()->sync($otherOrgIds);
        }

        // Send credentials email
        try {
            $name = $studentLeader->first_name . ' ' . $studentLeader->last_name;
            \Illuminate\Support\Facades\Mail::to($studentLeader->email)->send(new \App\Mail\AccountCredentialsMail(
                $studentLeader->email,
                $defaultPassword,
                $name,
                'Student Leader'
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send credentials email to student leader', [
                'user_id' => $studentLeader->id,
                'email' => $studentLeader->email,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.student-leaders.index')->with('success', 'Student leader created successfully.');
    }

    public function edit($id)
    {
        $studentLeader = User::where('role', 3)->with(['organization', 'otherOrganizations'])->findOrFail($id);
        $organizations = \App\Models\Organization::orderBy('name')->get();
        return view('admin.student-leaders.edit', compact('studentLeader', 'organizations'));
    }

    public function update(Request $request, $id)
    {
        $studentLeader = User::where('role', 3)->findOrFail($id);

        $data = $request->validate([
            'user_id' => ['required', 'string', 'max:50', 'unique:users,user_id,' . $studentLeader->id, new \App\Rules\UserIdByRole(3)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $studentLeader->id,
            'contact_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_ids' => 'nullable|array|max:5',
            'organization_ids.*' => 'integer|exists:organizations,id',
        ]);

        // Validate: Student leader must belong to at least one organization
        $allOrgIds = collect($data['organization_ids'] ?? []);
        if (!empty($data['organization_id'])) {
            $allOrgIds->push($data['organization_id']);
        }
        $allOrgIds = $allOrgIds->unique()->values();
        
        if ($allOrgIds->isEmpty()) {
            return back()->withErrors(['organization_ids' => 'Student leader must belong to at least one organization.'])->withInput();
        }
        
        if ($allOrgIds->count() > 5) {
            return back()->withErrors(['organization_ids' => 'Student leader can belong to a maximum of 5 organizations.'])->withInput();
        }

        // Validate: Each organization can have maximum 20 student leaders
        foreach ($allOrgIds as $orgId) {
            $currentCount = User::where('role', 3)
                ->where(function($q) use ($orgId) {
                    $q->where('organization_id', $orgId)
                      ->orWhereHas('otherOrganizations', function($oq) use ($orgId) {
                          $oq->where('organizations.id', $orgId);
                      });
                })
                ->where('id', '!=', $studentLeader->id)
                ->count();
            
            if ($currentCount >= 20) {
                $org = \App\Models\Organization::find($orgId);
                return back()->withErrors(['organization_ids' => "Organization '{$org->name}' already has the maximum of 20 student leaders."])->withInput();
            }
        }

        $studentLeader->user_id = $data['user_id'];
        $studentLeader->first_name = $data['first_name'];
        $studentLeader->middle_name = $data['middle_name'] ?? null;
        $studentLeader->last_name = $data['last_name'];
        $studentLeader->email = $data['email'];
        $studentLeader->contact_number = $data['contact_number'] ?? null;
        $studentLeader->organization_id = $data['organization_id'] ?? null;
        
        if (!empty($data['password'])) {
            $studentLeader->password = bcrypt($data['password']);
        }
        $studentLeader->save();

        // Sync other organizations (many-to-many)
        // Remove the primary organization from the list to avoid duplication
        $otherOrgIds = $allOrgIds->reject(function($orgId) use ($data) {
            return $orgId == ($data['organization_id'] ?? null);
        })->toArray();
        
        if (\Illuminate\Support\Facades\Schema::hasTable('organization_user')) {
            $studentLeader->otherOrganizations()->sync($otherOrgIds);
        }

        return redirect()->route('admin.student-leaders.index')->with('success', 'Student leader updated successfully.');
    }

    public function destroy($id)
    {
        $studentLeader = User::where('role', 3)->findOrFail($id);
        $studentLeader->delete();
        return redirect()->route('admin.student-leaders.index')->with('success', 'Student leader deleted.');
    }

    public function suspend($id)
    {
        $studentLeader = User::where('role', 3)->findOrFail($id);
        $studentLeader->suspended = true;
        $studentLeader->save();
        return redirect()->route('admin.student-leaders.index')->with('success', 'Student leader suspended.');
    }

    public function resume($id)
    {
        $studentLeader = User::where('role', 3)->findOrFail($id);
        $studentLeader->suspended = false;
        $studentLeader->save();
        return redirect()->route('admin.student-leaders.index')->with('success', 'Student leader resumed.');
    }
}
