<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    // List all assistants (role 3)
    public function index()
    {
        $assistants = User::where('role', 3)
            ->with(['department', 'organization', 'otherOrganizations', 'supervisor'])
            ->orderBy('last_name')
            ->get();
        return view('admin.assistants.index', compact('assistants'));
    }

    public function create()
    {
        $organizations = \App\Models\Organization::orderBy('name')->get();
        return view('admin.assistants.create', compact('organizations'));
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

        // Validate: Assistant must belong to at least one organization
        $allOrgIds = collect($data['organization_ids'] ?? []);
        if (!empty($data['organization_id'])) {
            $allOrgIds->push($data['organization_id']);
        }
        $allOrgIds = $allOrgIds->unique()->values();
        
        if ($allOrgIds->isEmpty()) {
            return back()->withErrors(['organization_ids' => 'Assistant must belong to at least one organization.'])->withInput();
        }
        
        if ($allOrgIds->count() > 5) {
            return back()->withErrors(['organization_ids' => 'Assistant can belong to a maximum of 5 organizations.'])->withInput();
        }

        // Validate: Each organization can have maximum 20 assistants
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
                return back()->withErrors(['organization_ids' => "Organization '{$org->name}' already has the maximum of 20 assistants."])->withInput();
            }
        }

        // Use default password "password"
        $defaultPassword = 'password';
        
        $assistant = User::create([
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
            $assistant->otherOrganizations()->sync($otherOrgIds);
        }

        // Send credentials email
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

        return redirect()->route('admin.assistants.index')->with('success', 'Assistant created successfully.');
    }

    public function edit($id)
    {
        $assistant = User::where('role', 3)->with(['organization', 'otherOrganizations'])->findOrFail($id);
        $organizations = \App\Models\Organization::orderBy('name')->get();
        return view('admin.assistants.edit', compact('assistant', 'organizations'));
    }

    public function update(Request $request, $id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);

        $data = $request->validate([
            'user_id' => ['required', 'string', 'max:50', 'unique:users,user_id,' . $assistant->id, new \App\Rules\UserIdByRole(3)],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:1|regex:/^[A-Za-z]$/',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $assistant->id,
            'contact_number' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'organization_id' => 'nullable|exists:organizations,id',
            'organization_ids' => 'nullable|array|max:5',
            'organization_ids.*' => 'integer|exists:organizations,id',
        ]);

        // Validate: Assistant must belong to at least one organization
        $allOrgIds = collect($data['organization_ids'] ?? []);
        if (!empty($data['organization_id'])) {
            $allOrgIds->push($data['organization_id']);
        }
        $allOrgIds = $allOrgIds->unique()->values();
        
        if ($allOrgIds->isEmpty()) {
            return back()->withErrors(['organization_ids' => 'Assistant must belong to at least one organization.'])->withInput();
        }
        
        if ($allOrgIds->count() > 5) {
            return back()->withErrors(['organization_ids' => 'Assistant can belong to a maximum of 5 organizations.'])->withInput();
        }

        // Validate: Each organization can have maximum 20 assistants
        foreach ($allOrgIds as $orgId) {
            $currentCount = User::where('role', 3)
                ->where(function($q) use ($orgId) {
                    $q->where('organization_id', $orgId)
                      ->orWhereHas('otherOrganizations', function($oq) use ($orgId) {
                          $oq->where('organizations.id', $orgId);
                      });
                })
                ->where('id', '!=', $assistant->id)
                ->count();
            
            if ($currentCount >= 20) {
                $org = \App\Models\Organization::find($orgId);
                return back()->withErrors(['organization_ids' => "Organization '{$org->name}' already has the maximum of 20 assistants."])->withInput();
            }
        }

        $assistant->user_id = $data['user_id'];
        $assistant->first_name = $data['first_name'];
        $assistant->middle_name = $data['middle_name'] ?? null;
        $assistant->last_name = $data['last_name'];
        $assistant->email = $data['email'];
        $assistant->contact_number = $data['contact_number'] ?? null;
        $assistant->organization_id = $data['organization_id'] ?? null;
        
        if (!empty($data['password'])) {
            $assistant->password = bcrypt($data['password']);
        }
        $assistant->save();

        // Sync other organizations (many-to-many)
        // Remove the primary organization from the list to avoid duplication
        $otherOrgIds = $allOrgIds->reject(function($orgId) use ($data) {
            return $orgId == ($data['organization_id'] ?? null);
        })->toArray();
        
        if (\Illuminate\Support\Facades\Schema::hasTable('organization_user')) {
            $assistant->otherOrganizations()->sync($otherOrgIds);
        }

        return redirect()->route('admin.assistants.index')->with('success', 'Assistant updated successfully.');
    }

    public function destroy($id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);
        $assistant->delete();
        return redirect()->route('admin.assistants.index')->with('success', 'Assistant deleted.');
    }

    public function suspend($id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);
        $assistant->suspended = true;
        $assistant->save();
        return redirect()->route('admin.assistants.index')->with('success', 'Assistant suspended.');
    }

    public function resume($id)
    {
        $assistant = User::where('role', 3)->findOrFail($id);
        $assistant->suspended = false;
        $assistant->save();
        return redirect()->route('admin.assistants.index')->with('success', 'Assistant resumed.');
    }
}
