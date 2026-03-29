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
        // Check if staff_id column exists and user_id doesn't exist
        if (Schema::hasColumn('users', 'staff_id') && !Schema::hasColumn('users', 'user_id')) {
            // Drop the unique index on staff_id if it exists
            try {
                $indexes = DB::select("SHOW INDEX FROM `users` WHERE Column_name = 'staff_id' AND Non_unique = 0");
                if (!empty($indexes)) {
                    DB::statement('ALTER TABLE `users` DROP INDEX `users_staff_id_unique`');
                }
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }
            
            // Rename staff_id to user_id
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `staff_id` `user_id` VARCHAR(255) NULL');
            
            // Add unique index on user_id
            try {
                $indexes = DB::select("SHOW INDEX FROM `users` WHERE Column_name = 'user_id' AND Non_unique = 0");
                if (empty($indexes)) {
                    DB::statement('ALTER TABLE `users` ADD UNIQUE INDEX `users_user_id_unique` (`user_id`)');
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
        // Reverse: Rename user_id back to staff_id
        if (Schema::hasColumn('users', 'user_id') && !Schema::hasColumn('users', 'staff_id')) {
            // Drop the unique index on user_id if it exists
            try {
                $indexes = DB::select("SHOW INDEX FROM `users` WHERE Column_name = 'user_id' AND Non_unique = 0");
                if (!empty($indexes)) {
                    DB::statement('ALTER TABLE `users` DROP INDEX `users_user_id_unique`');
                }
            } catch (\Exception $e) {
                // Index might not exist, ignore
            }
            
            // Rename user_id back to staff_id
            DB::statement('ALTER TABLE `users` CHANGE COLUMN `user_id` `staff_id` VARCHAR(255) NULL');
            
            // Add unique index on staff_id
            try {
                $indexes = DB::select("SHOW INDEX FROM `users` WHERE Column_name = 'staff_id' AND Non_unique = 0");
                if (empty($indexes)) {
                    DB::statement('ALTER TABLE `users` ADD UNIQUE INDEX `users_staff_id_unique` (`staff_id`)');
                }
            } catch (\Exception $e) {
                // Index might already exist, ignore
            }
        }
    }
};
