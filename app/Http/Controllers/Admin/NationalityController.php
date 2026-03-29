<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use Illuminate\Http\Request;

class NationalityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nationalities = Nationality::orderBy('name')->paginate(20);
        return view('admin.nationalities.index', compact('nationalities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.nationalities.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:nationalities,name',
            'code' => 'nullable|string|max:3',
            'is_active' => 'nullable|boolean',
        ]);

        Nationality::create([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.nationalities.index')
            ->with('success', 'Nationality created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Nationality $nationality)
    {
        return view('admin.nationalities.show', compact('nationality'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nationality $nationality)
    {
        return view('admin.nationalities.edit', compact('nationality'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nationality $nationality)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:nationalities,name,' . $nationality->id,
            'code' => 'nullable|string|max:3',
            'is_active' => 'nullable|boolean',
        ]);

        $nationality->update([
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.nationalities.index')
            ->with('success', 'Nationality updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nationality $nationality)
    {
        // Check if nationality is being used
        $usageCount = $nationality->personalInformation()->count();
        
        if ($usageCount > 0) {
            return redirect()->route('admin.nationalities.index')
                ->with('error', 'Cannot delete nationality. It is being used by ' . $usageCount . ' record(s).');
        }

        $nationality->delete();

        return redirect()->route('admin.nationalities.index')
            ->with('success', 'Nationality deleted successfully.');
    }
}
