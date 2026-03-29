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
        // Step 1: Clear user_id for staff (role = 2) in users table, except admins (role = 4)
        DB::table('users')
            ->where('role', 2) // Staff role
            ->whereNotNull('user_id')
            ->update(['user_id' => null]);
        
        // Step 2: Clear employee_id in staff_information for staff that are not admins
        // Get staff user_ids that are not admins
        $staffUserIds = DB::table('users')
            ->where('role', 2)
            ->pluck('id');
        
        if ($staffUserIds->isNotEmpty()) {
            DB::table('staff_information')
                ->whereIn('user_id', $staffUserIds)
                ->whereNotNull('employee_id')
                ->update(['employee_id' => null]);
        }
        
        // Step 3: Rename user_id to staff_id in users table (using raw SQL)
        DB::statement('ALTER TABLE `users` CHANGE COLUMN `user_id` `staff_id` VARCHAR(255) NULL');
        
        // Step 4: Rename employee_id to staff_id in staff_information table (using raw SQL)
        DB::statement('ALTER TABLE `staff_information` CHANGE COLUMN `employee_id` `staff_id` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Rename staff_id back to original names (using raw SQL)
        DB::statement('ALTER TABLE `staff_information` CHANGE COLUMN `staff_id` `employee_id` VARCHAR(255) NULL');
        DB::statement('ALTER TABLE `users` CHANGE COLUMN `staff_id` `user_id` VARCHAR(255) NULL');
    }
};
