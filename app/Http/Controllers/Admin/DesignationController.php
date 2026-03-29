<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use Illuminate\Http\Request;
use App\Services\CacheService;

class DesignationController extends Controller
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
     * Display a listing of designations
     */
    public function index()
    {
        $designations = CacheService::getDesignations();
        return view('admin.designations.index', compact('designations'));
    }

    /**
     * Show the form for creating a new designation
     */
    public function create()
    {
        return view('admin.designations.create');
    }

    /**
     * Store a newly created designation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:designations,name',
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ]);

        Designation::create([
            'name' => $validated['name'],
            'features' => $validated['features'] ?? [],
        ]);

        // Cache is automatically cleared via CachesReferenceData trait

        return redirect()->route('admin.designations.index')
            ->with('success', 'Designation created successfully.');
    }

    /**
     * Show the form for editing a designation
     */
    public function edit(Designation $designation)
    {
        return view('admin.designations.edit', compact('designation'));
    }

    /**
     * Update a designation
     */
    public function update(Request $request, Designation $designation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:designations,name,' . $designation->id,
            'features' => 'nullable|array',
            'features.*' => 'string|max:255',
        ]);

        $designation->update([
            'name' => $validated['name'],
            'features' => $validated['features'] ?? [],
        ]);

        return redirect()->route('admin.designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    /**
     * Remove a designation
     */
    public function destroy(Designation $designation)
    {
        // Check if designation is being used
        $staffCount = \App\Models\Staff::where('designation', $designation->name)->count();
        
        if ($staffCount > 0) {
            return redirect()->route('admin.designations.index')
                ->with('error', "Cannot delete designation. It is being used by {$staffCount} staff member(s).");
        }

        $designation->delete();

        return redirect()->route('admin.designations.index')
            ->with('success', 'Designation deleted successfully.');
    }
}

