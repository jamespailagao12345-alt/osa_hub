<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Services\CacheService;

class DepartmentController extends Controller
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
     * Display a listing of departments
     */
    public function index()
    {
        $departments = Department::with(['courses.sections'])->get();
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department
     */
    public function create()
    {
        return view('admin.departments.create');
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'courses' => 'nullable|array',
            'courses.*.name' => 'required|string|max:255',
            'courses.*.sections' => 'nullable|array',
            'courses.*.sections.*' => 'required|string|max:255',
        ]);

        $department = Department::create([
            'name' => $validated['name'],
        ]);

        // Create courses and sections
        if (isset($validated['courses'])) {
            foreach ($validated['courses'] as $courseData) {
                $course = \App\Models\Course::create([
                    'department_id' => $department->id,
                    'name' => $courseData['name'],
                ]);

                // Create sections for this course
                if (isset($courseData['sections']) && is_array($courseData['sections'])) {
                    foreach ($courseData['sections'] as $sectionName) {
                        if (!empty(trim($sectionName))) {
                            \App\Models\Section::create([
                                'course_id' => $course->id,
                                'name' => trim($sectionName),
                            ]);
                        }
                    }
                }
            }
        }

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully with courses and sections.');
    }

    /**
     * Show the form for editing the specified department
     */
    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('admin.departments.edit', compact('department'));
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department
     */
    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        // Check if department has courses
        if ($department->courses()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department. It has associated courses. Please delete or reassign courses first.');
        }

        // Check if department has users
        if ($department->users()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department. It has associated users. Please reassign users first.');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Store a new course for a department
     */
    public function storeCourse(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sections' => 'nullable|array',
            'sections.*' => 'required|string|max:255',
        ]);

        $course = \App\Models\Course::create([
            'name' => $validated['name'],
            'department_id' => $department->id,
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

        CacheService::clearCourseCache($department->id);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Course created successfully.');
    }

    /**
     * Update a course
     */
    public function updateCourse(Request $request, Department $department, \App\Models\Course $course)
    {
        // Verify course belongs to department
        if ($course->department_id !== $department->id) {
            abort(403, 'Course does not belong to this department.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sections' => 'nullable|array',
            'sections.*' => 'required|string|max:255',
            'section_ids' => 'nullable|array',
            'section_ids.*' => 'nullable|integer|exists:sections,id',
        ]);

        $course->update([
            'name' => $validated['name'],
        ]);

        // Handle sections update
        if (isset($validated['sections']) && is_array($validated['sections'])) {
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
            
            // Delete sections that were removed
            if (!empty($allCurrentSectionIds)) {
                $sectionsToDelete = array_diff($allCurrentSectionIds, $processedSectionIds);
                if (!empty($sectionsToDelete)) {
                    \App\Models\Section::where('course_id', $course->id)
                        ->whereIn('id', $sectionsToDelete)
                        ->delete();
                }
            }
        }

        CacheService::clearCourseCache($department->id);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Delete a course
     */
    public function destroyCourse(Department $department, \App\Models\Course $course)
    {
        // Verify course belongs to department
        if ($course->department_id !== $department->id) {
            abort(403, 'Course does not belong to this department.');
        }

        // Check if course has users
        if ($course->users()->count() > 0) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete course. It has associated users. Please reassign users first.');
        }

        // Delete sections
        \App\Models\Section::where('course_id', $course->id)->delete();
        
        $course->delete();

        CacheService::clearCourseCache($department->id);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Course deleted successfully.');
    }
}
