<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\Staff;

class SyncStudentAndEmployeeIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:student-employee-ids {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync user_id from users table to student_id in student_information and employee_id in staff_information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->info('Starting sync of student_id and employee_id...');
        $this->newLine();

        // Sync student_id for students (role 1) and student leaders (role 3)
        $this->info('Syncing student_id for students and student leaders...');
        $students = User::whereIn('role', [1, 3])->whereNotNull('user_id')->get();
        $studentCount = 0;
        $studentUpdated = 0;
        $studentSkipped = 0;

        foreach ($students as $user) {
            $studentCount++;
            $student = Student::where('user_id', $user->id)->first();
            
            if ($student) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would update student_id for user {$user->id} ({$user->email}): '{$user->user_id}'");
                    $studentUpdated++;
                } else {
                    $student->student_id = $user->user_id;
                    $student->save();
                    $this->line("  ✓ Updated student_id for user {$user->id} ({$user->email}): '{$user->user_id}'");
                    $studentUpdated++;
                }
            } else {
                $this->warn("  ⚠ No student_information record found for user {$user->id} ({$user->email})");
                $studentSkipped++;
            }
        }

        $this->info("Students processed: {$studentCount} | Updated: {$studentUpdated} | Skipped: {$studentSkipped}");
        $this->newLine();

        // Sync employee_id for staff (role 2) and admins (role 4)
        $this->info('Syncing employee_id for staff and admins...');
        $staff = User::whereIn('role', [2, 4])->whereNotNull('user_id')->get();
        $staffCount = 0;
        $staffUpdated = 0;
        $staffSkipped = 0;

        foreach ($staff as $user) {
            $staffCount++;
            $staffRecord = Staff::where('user_id', $user->id)
                ->orWhereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
                ->first();
            
            if ($staffRecord) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would update employee_id for user {$user->id} ({$user->email}): '{$user->user_id}'");
                    $staffUpdated++;
                } else {
                    $staffRecord->employee_id = $user->user_id;
                    $staffRecord->save();
                    $this->line("  ✓ Updated employee_id for user {$user->id} ({$user->email}): '{$user->user_id}'");
                    $staffUpdated++;
                }
            } else {
                $this->warn("  ⚠ No staff_information record found for user {$user->id} ({$user->email})");
                $staffSkipped++;
            }
        }

        $this->info("Staff processed: {$staffCount} | Updated: {$staffUpdated} | Skipped: {$staffSkipped}");
        $this->newLine();

        if ($dryRun) {
            $this->info('DRY RUN completed. Run without --dry-run to apply changes.');
        } else {
            $this->info('✓ Sync completed successfully!');
        }

        return Command::SUCCESS;
    }
}
