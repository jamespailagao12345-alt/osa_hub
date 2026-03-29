<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Clear user_id/staff_id for staff (role = 2) in users table, except admins with null user_id/staff_id
        // Check which column name exists
        $hasUserId = Schema::hasColumn('users', 'user_id');
        $hasStaffId = Schema::hasColumn('users', 'staff_id');
        
        if ($hasUserId) {
            DB::table('users')
                ->where('role', 2) // Staff role
                ->whereNotNull('user_id')
                ->update(['user_id' => null]);
        } elseif ($hasStaffId) {
            DB::table('users')
                ->where('role', 2) // Staff role
                ->whereNotNull('staff_id')
                ->update(['staff_id' => null]);
        }
        
        // Step 2: Clear employee_id/staff_id in staff_information for all staff (except admins with null user_id/staff_id)
        // Get admin user IDs that have null user_id/staff_id
        $adminUserIds = [];
        if ($hasUserId) {
            $adminUserIds = DB::table('users')
                ->where('role', 4) // Assuming role 4 is admin
                ->whereNull('user_id')
                ->pluck('id')
                ->toArray();
        } elseif ($hasStaffId) {
            $adminUserIds = DB::table('users')
                ->where('role', 4) // Assuming role 4 is admin
                ->whereNull('staff_id')
                ->pluck('id')
                ->toArray();
        }
        
        // Check which column name exists in staff_information
        $hasEmployeeId = Schema::hasColumn('staff_information', 'employee_id');
        $hasStaffInfoStaffId = Schema::hasColumn('staff_information', 'staff_id');
        
        if ($hasEmployeeId) {
            // Clear employee_id for all staff_information records except those linked to admins with null user_id/staff_id
            if (!empty($adminUserIds)) {
                DB::table('staff_information')
                    ->whereNotIn('user_id', $adminUserIds)
                    ->whereNotNull('employee_id')
                    ->update(['employee_id' => null]);
            } else {
                // If no admins with null user_id/staff_id, clear all employee_id
                DB::table('staff_information')
                    ->whereNotNull('employee_id')
                    ->update(['employee_id' => null]);
            }
        } elseif ($hasStaffInfoStaffId) {
            // Clear staff_id for all staff_information records except those linked to admins
            if (!empty($adminUserIds)) {
                DB::table('staff_information')
                    ->whereNotIn('user_id', $adminUserIds)
                    ->whereNotNull('staff_id')
                    ->update(['staff_id' => null]);
            } else {
                // If no admins with null user_id/staff_id, clear all staff_id
                DB::table('staff_information')
                    ->whereNotNull('staff_id')
                    ->update(['staff_id' => null]);
            }
        }
        
        // Step 3: Rename user_id to staff_id in users table (using raw SQL for compatibility)
        if ($hasUserId && !$hasStaffId) {
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `user_id` `staff_id` VARCHAR(255) NULL');
        }
        
        // Step 4: Rename employee_id to staff_id in staff_information table (using raw SQL)
        if ($hasEmployeeId && !$hasStaffInfoStaffId) {
            DB::statement('ALTER TABLE `staff_information` CHANGE COLUMN `employee_id` `staff_id` VARCHAR(255) NULL');
        }
        
        // Step 5: Add unique constraint to staff_id columns if they don't exist
        if (Schema::hasColumn('users', 'staff_id')) {
            try {
                // Check if unique index already exists
                $indexes = DB::select("SHOW INDEX FROM `users` WHERE Column_name = 'staff_id' AND Non_unique = 0");
                if (empty($indexes)) {
                    DB::statement('ALTER TABLE `users` ADD UNIQUE INDEX `users_staff_id_unique` (`staff_id`)');
                }
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        }
        
        if (Schema::hasColumn('staff_information', 'staff_id')) {
            try {
                // Check if unique index already exists
                $indexes = DB::select("SHOW INDEX FROM `staff_information` WHERE Column_name = 'staff_id' AND Non_unique = 0");
                if (empty($indexes)) {
                    DB::statement('ALTER TABLE `staff_information` ADD UNIQUE INDEX `staff_information_staff_id_unique` (`staff_id`)');
                }
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Rename staff_id back to user_id/employee_id (using raw SQL)
        if (Schema::hasColumn('users', 'staff_id') && !Schema::hasColumn('users', 'user_id')) {
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `staff_id` `user_id` VARCHAR(255) NULL');
        }
        
        if (Schema::hasColumn('staff_information', 'staff_id') && !Schema::hasColumn('staff_information', 'employee_id')) {
            DB::statement('ALTER TABLE `staff_information` CHANGE COLUMN `staff_id` `employee_id` VARCHAR(255) NULL');
        }
    }
};
