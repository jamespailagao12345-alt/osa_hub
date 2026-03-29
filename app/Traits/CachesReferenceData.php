<?php

namespace App\Traits;

use App\Services\CacheService;

trait CachesReferenceData
{
    /**
     * Boot the trait and set up cache clearing on model events
     */
    public static function bootCachesReferenceData()
    {
        static::created(function ($model) {
            static::clearRelatedCache($model);
        });

        static::updated(function ($model) {
            static::clearRelatedCache($model);
        });

        static::deleted(function ($model) {
            static::clearRelatedCache($model);
        });
    }

    /**
     * Clear cache related to this model
     */
    protected static function clearRelatedCache($model)
    {
        $className = class_basename($model);
        
        switch ($className) {
            case 'Department':
                CacheService::clearDepartmentCache($model->id ?? null);
                break;
            case 'Course':
                CacheService::clearCourseCache($model->department_id ?? null);
                break;
            case 'Organization':
                CacheService::clearOrganizationCache($model->department_id ?? null);
                break;
            case 'Designation':
                CacheService::clearReferenceData();
                break;
            case 'Nationality':
                CacheService::clearReferenceData();
                break;
            case 'Scholarship':
                CacheService::clearReferenceData();
                break;
            case 'User':
                // Clear role cache if role changed
                if ($model->isDirty('role')) {
                    CacheService::clearUserRoleCache();
                }
                break;
        }
    }
}

