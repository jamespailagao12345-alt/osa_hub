<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Services\CacheService;

class CourseController extends Controller
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
     * Display a listing of courses
     */
    public function index()
    {
        $courses = Course::with(['department', 'sections'])->orderBy('department_id')->orderBy('name')->get();
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $departments = CacheService::getDepartments();
        return view('admin.courses.create', compact('departments'));
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'sections' => 'nullable|array',
            'sections.*' => 'required|string|max:255',
        ]);

        $course = Course::create([
            'name' => $validated['name'],
            'department_id' => $validated['department_id'],
        ]);

        // Create sections for this course
        if (isset($validated['sections']) && is_array($validated['sections'])) {
            foreach ($validated['sections'] as $sectionName) {
                if (!empty(trim($sectionName))) {
                    \App\Models\Section::create([
                        'course_id' => $course->id,
                        'name' => trim($sectionName),
                    ]);
                }
            }
        }

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully with sections.');
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit($id)
    {
        $course = Course::with('sections')->findOrFail($id);
        // Ensure sections are fresh
        $course->load('sections');
        $departments = CacheService::getDepartments();
        return view('admin.courses.edit', compact('course', 'departments'));
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'edit_sections' => 'nullable|boolean',
            'sections' => 'nullable|array',
            'sections.*' => 'required|string|max:255',
            'section_ids' => 'nullable|array',
            'section_ids.*' => 'nullable|integer|exists:sections,id',
        ]);

        $course->update([
            'name' => $validated['name'],
            'department_id' => $validated['department_id'],
        ]);

        // Handle sections update - only if edit_sections checkbox was checked
        // If checkbox was not checked, leave existing sections unchanged
        if (isset($validated['edit_sections']) && $validated['edit_sections'] && 
            isset($validated['sections']) && is_array($validated['sections']) && count($validated['sections']) > 0) {
            $existingSectionIds = $validated['section_ids'] ?? [];
            $sectionNames = $validated['sections'];
            
            // Get all current section IDs for this course
            $allCurrentSectionIds = \App\Models\Section::where('course_id', $course->id)
                ->pluck('id')
                ->toArray();
            
            $processedSectionIds = [];
            
            // Update existing sections or create new ones
            foreach ($sectionNames as $index => $sectionName) {
                if (!empty(trim($sectionName))) {
                    $sectionId = isset($existingSectionIds[$index]) && !empty($existingSectionIds[$index]) 
                        ? $existingSectionIds[$index] 
                        : null;
                    
                    if ($sectionId && in_array($sectionId, $allCurrentSectionIds)) {
                        // Update existing section
                        \App\Models\Section::where('id', $sectionId)
                            ->where('course_id', $course->id)
                            ->update(['name' => trim($sectionName)]);
                        $processedSectionIds[] = $sectionId;
                    } else {
                        // Create new section
                        $newSection = \App\Models\Section::create([
                            'course_id' => $course->id,
                            'name' => trim($sectionName),
                        ]);
                        $processedSectionIds[] = $newSection->id;
                    }
                }
            }
            
            // Delete sections that were removed (not in processed list)
            if (!empty($allCurrentSectionIds)) {
                $sectionsToDelete = array_diff($allCurrentSectionIds, $processedSectionIds);
                if (!empty($sectionsToDelete)) {
                    \App\Models\Section::where('course_id', $course->id)
                        ->whereIn('id', $sectionsToDelete)
                        ->delete();
                }
            }
        }
        // If sections are not provided or empty, leave existing sections unchanged

        // Clear course cache to ensure fresh data
        CacheService::clearCourseCache($course->department_id);
        
        // Reload course with fresh sections
        $course->refresh();
        $course->load('sections');
        
        // Redirect back to edit page to show updated values
        $message = isset($validated['sections']) && is_array($validated['sections']) && count($validated['sections']) > 0
            ? 'Course updated successfully with sections.'
            : 'Course updated successfully.';
        
        return redirect()->route('admin.courses.edit', $course->id)
            ->with('success', $message);
    }

    /**
     * Remove the specified course
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Check if course has users
        if ($course->users()->count() > 0) {
            return redirect()->route('admin.courses.index')
                ->with('error', 'Cannot delete course. It has associated users. Please reassign users first.');
        }

        // Delete sections (they will be deleted via cascade, but being explicit)
        \App\Models\Section::where('course_id', $course->id)->delete();
        
        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted successfully.');
    }
}
