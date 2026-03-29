<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\Department;
use App\Models\Course;
use App\Models\Organization;
use App\Models\Designation;
use App\Models\Nationality;
use App\Models\Scholarship;

class CacheService
{
    /**
     * Cache duration in seconds
     */
    const CACHE_DURATION = 3600; // 1 hour
    const SHORT_CACHE_DURATION = 300; // 5 minutes
    const LONG_CACHE_DURATION = 86400; // 24 hours

    /**
     * Get all departments (cached)
     */
    public static function getDepartments()
    {
        return Cache::remember('departments.all', self::CACHE_DURATION, function () {
            return Department::orderBy('name')->get();
        });
    }

    /**
     * Get all courses (cached)
     */
    public static function getCourses()
    {
        return Cache::remember('courses.all', self::CACHE_DURATION, function () {
            return Course::orderBy('name')->get();
        });
    }

    /**
     * Get courses by department (cached)
     */
    public static function getCoursesByDepartment($departmentId)
    {
        return Cache::remember("courses.department.{$departmentId}", self::CACHE_DURATION, function () use ($departmentId) {
            return Course::where('department_id', $departmentId)->orderBy('name')->get();
        });
    }

    /**
     * Get all organizations (cached)
     */
    public static function getOrganizations()
    {
        return Cache::remember('organizations.all', self::CACHE_DURATION, function () {
            return Organization::orderBy('name')->get();
        });
    }

    /**
     * Get organizations by department (cached)
     */
    public static function getOrganizationsByDepartment($departmentId = null)
    {
        $key = $departmentId ? "organizations.department.{$departmentId}" : 'organizations.unassigned';
        
        return Cache::remember($key, self::CACHE_DURATION, function () use ($departmentId) {
            $query = Organization::orderBy('name');
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            } else {
                $query->whereNull('department_id');
            }
            return $query->get();
        });
    }

    /**
     * Get all designations (cached)
     */
    public static function getDesignations()
    {
        return Cache::remember('designations.all', self::LONG_CACHE_DURATION, function () {
            return Designation::orderBy('name')->get();
        });
    }

    /**
     * Get all nationalities (cached)
     */
    public static function getNationalities()
    {
        return Cache::remember('nationalities.all', self::LONG_CACHE_DURATION, function () {
            return Nationality::orderBy('name')->get();
        });
    }

    /**
     * Get all scholarships (cached)
     */
    public static function getScholarships()
    {
        return Cache::remember('scholarships.all', self::CACHE_DURATION, function () {
            return Scholarship::orderBy('name')->get();
        });
    }

    /**
     * Get staff user IDs (cached)
     */
    public static function getStaffUserIds()
    {
        return Cache::remember('users.staff.ids', self::SHORT_CACHE_DURATION, function () {
            return \App\Models\User::where('role', 2)->pluck('id')->toArray();
        });
    }

    /**
     * Get admin user IDs (cached)
     */
    public static function getAdminUserIds()
    {
        return Cache::remember('users.admin.ids', self::SHORT_CACHE_DURATION, function () {
            return \App\Models\User::where('role', 4)->pluck('id')->toArray();
        });
    }

    /**
     * Clear all reference data cache
     */
    public static function clearReferenceData()
    {
        Cache::forget('departments.all');
        Cache::forget('courses.all');
        Cache::forget('organizations.all');
        Cache::forget('designations.all');
        Cache::forget('nationalities.all');
        Cache::forget('scholarships.all');
        
        // Clear department-specific caches
        Cache::forget('courses.department.*');
        Cache::forget('organizations.department.*');
        Cache::forget('organizations.unassigned');
    }

    /**
     * Clear department-related cache
     */
    public static function clearDepartmentCache($departmentId = null)
    {
        if ($departmentId) {
            Cache::forget("courses.department.{$departmentId}");
            Cache::forget("organizations.department.{$departmentId}");
        }
        Cache::forget('departments.all');
        Cache::forget('courses.all');
    }

    /**
     * Clear organization cache
     */
    public static function clearOrganizationCache($departmentId = null)
    {
        Cache::forget('organizations.all');
        if ($departmentId) {
            Cache::forget("organizations.department.{$departmentId}");
        } else {
            Cache::forget('organizations.unassigned');
        }
    }

    /**
     * Clear course cache
     */
    public static function clearCourseCache($departmentId = null)
    {
        Cache::forget('courses.all');
        if ($departmentId) {
            Cache::forget("courses.department.{$departmentId}");
        }
    }

    /**
     * Clear user role cache
     */
    public static function clearUserRoleCache()
    {
        Cache::forget('users.staff.ids');
        Cache::forget('users.admin.ids');
    }

    /**
     * Get event with optimized relationships (cached for short duration)
     */
    public static function getEventWithRelations($eventId)
    {
        return Cache::remember("event.{$eventId}.relations", self::SHORT_CACHE_DURATION, function () use ($eventId) {
            return \App\Models\Event::with([
                'creator:id,first_name,last_name,email',
                'organization:id,name',
                'requirements',
                'participants.user:id,first_name,last_name,email',
            ])->find($eventId);
        });
    }

    /**
     * Clear event cache
     */
    public static function clearEventCache($eventId = null)
    {
        if ($eventId) {
            Cache::forget("event.{$eventId}.relations");
        } else {
            // Clear all event caches (use tag if available)
            Cache::flush(); // Note: This clears ALL cache, use tags in production with Redis
        }
    }
}

