<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Services\CacheService;

class OrganizationController extends Controller
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
        $organizations = Organization::with(['department', 'staff'])->orderBy('name')->get();
        return view('admin.organizations.index', compact('organizations'));
    }

    /**
     * Show the form for creating a new organization
     */
    public function create()
    {
        $departments = CacheService::getDepartments();
        // Get all staff from staff_information table
        $allStaff = \App\Models\Staff::with(['department', 'user'])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        
        return view('admin.organizations.create', compact('departments', 'allStaff'));
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
            'official_email' => 'required|email|max:255',
            'mailing_address' => 'nullable|string',
            'date_established' => 'nullable|date',
            'is_special' => 'boolean',
            'staff_id' => 'nullable|exists:staff_information,id',
        ]);
        
        // Convert empty string to null for department_id
        if (isset($validated['department_id']) && $validated['department_id'] === '') {
            $validated['department_id'] = null;
        }

        // Set default for is_special if not provided
        if (!isset($validated['is_special'])) {
            $validated['is_special'] = false;
        }

        $organization = Organization::create($validated);

        // Assign staff if provided
        if ($request->filled('staff_id')) {
            $organization->staff()->syncWithoutDetaching([$request->staff_id]);
        }

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    /**
     * Show the form for editing the specified organization
     */
    public function edit($id)
    {
        $organization = Organization::findOrFail($id);
        $departments = CacheService::getDepartments();
        return view('admin.organizations.edit', compact('organization', 'departments'));
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
            'official_email' => 'required|email|max:255',
            'mailing_address' => 'nullable|string',
            'date_established' => 'nullable|date',
            'is_special' => 'boolean',
        ]);
        
        // Convert empty string to null for department_id
        if (isset($validated['department_id']) && $validated['department_id'] === '') {
            $validated['department_id'] = null;
        }

        // Set default for is_special if not provided
        if (!isset($validated['is_special'])) {
            $validated['is_special'] = false;
        }

        $organization->update($validated);

        return redirect()->route('admin.organizations.index')
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
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Cannot delete organization. It has associated users. Please reassign users first.');
        }

        // Check if organization has other users (via pivot table)
        if ($organization->otherUsers()->count() > 0) {
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Cannot delete organization. It has associated members. Please remove members first.');
        }

        // Check if organization has staff members (via pivot table)
        if ($organization->staff()->count() > 0) {
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Cannot delete organization. It has associated staff members. Please remove staff assignments first.');
        }

        // Check if organization has primary staff members
        if ($organization->primaryStaff()->count() > 0) {
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Cannot delete organization. It has associated staff members. Please reassign staff first.');
        }

        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization deleted successfully.');
    }
}

