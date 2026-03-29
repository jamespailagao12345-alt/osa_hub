<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Services\CacheService;

class OrganizationManagementController extends Controller
{
    /**
     * Create a new controller instance.
     * Ensure only admins (role 4) can access these methods.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->check() || (int)auth()->user()->role !== 4) {
                abort(403, 'Unauthorized: Only administrators can access this page.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of organizations
     */
    public function index()
    {
        $organizations = Organization::with('department')->orderBy('name')->get();
        return view('admin.organizations-management.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new organization
     */
    public function create()
    {
        $departments = CacheService::getDepartments();
        return view('admin.organizations-management.create', compact('departments'));
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'official_email' => 'nullable|email|max:255',
            'mailing_address' => 'nullable|string',
            'date_established' => 'nullable|date',
            'is_special' => 'boolean',
        ]);

        Organization::create($validated);

        return redirect()->route('admin.organizations-management.index')
            ->with('success', 'Organization created successfully.');
    }

    /**
     * Show the form for editing the specified organization
     */
    public function edit($id)
    {
        $organization = Organization::findOrFail($id);
        $departments = CacheService::getDepartments();
        return view('admin.organizations-management.edit', compact('organization', 'departments'));
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'nullable|string|max:50',
            'department_id' => 'nullable|exists:departments,id',
            'official_email' => 'nullable|email|max:255',
            'mailing_address' => 'nullable|string',
            'date_established' => 'nullable|date',
            'is_special' => 'boolean',
        ]);

        $organization->update($validated);

        return redirect()->route('admin.organizations-management.index')
            ->with('success', 'Organization updated successfully.');
    }

    /**
     * Remove the specified organization
     */
    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);

        // Check if organization has users
        if ($organization->users()->count() > 0) {
            return redirect()->route('admin.organizations-management.index')
                ->with('error', 'Cannot delete organization. It has associated users. Please reassign users first.');
        }

        // Check if organization has other users (via pivot table)
        if ($organization->otherUsers()->count() > 0) {
            return redirect()->route('admin.organizations-management.index')
                ->with('error', 'Cannot delete organization. It has associated members. Please remove members first.');
        }

        $organization->delete();

        return redirect()->route('admin.organizations-management.index')
            ->with('success', 'Organization deleted successfully.');
    }
}
