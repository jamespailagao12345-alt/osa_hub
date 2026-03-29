<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Staff;
use App\Models\Student;
use App\Models\AssistantAssignment;

class VerifyAndSyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:verify-and-sync {--dry-run : Show what would be synced without actually syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify and sync all users to appropriate tables based on role:
    - Role 4 (admin) and Role 2 (staff) → staff_information
    - Role 1 (student) → student_information
    - Role 3 (assistant) → student_information AND student_leaders_information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }
        
        $observer = new UserObserver();
        $stats = [
            'role_2_synced' => 0,
            'role_4_synced' => 0,
            'role_1_synced' => 0,
            'role_3_synced_student' => 0,
            'role_3_synced_leaders' => 0,
            'errors' => 0,
        ];
        
        // Sync Role 4 (admin) and Role 2 (staff) to staff_information
        $this->info('Syncing Role 2 (staff) and Role 4 (admin) to staff_information...');
        $staffUsers = User::whereIn('role', [2, 4])->get();
        
        foreach ($staffUsers as $user) {
            try {
                if ($dryRun) {
                    $exists = Staff::where('user_id', $user->id)
                        ->orWhereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
                        ->exists();
                    
                    if (!$exists) {
                        $this->line("  [DRY] Would create staff_information for: {$user->email} (Role: {$user->role})");
                    } else {
                        $this->line("  [DRY] Would update staff_information for: {$user->email} (Role: {$user->role})");
                    }
                } else {
                    $this->syncUserToStaff($user);
                    if ($user->role == 2) {
                        $stats['role_2_synced']++;
                    } else {
                        $stats['role_4_synced']++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error syncing {$user->email}: " . $e->getMessage());
                $stats['errors']++;
            }
        }
        
        $this->newLine();
        
        // Sync Role 1 (student) to student_information
        $this->info('Syncing Role 1 (student) to student_information...');
        $studentUsers = User::where('role', 1)->get();
        
        foreach ($studentUsers as $user) {
            try {
                if ($dryRun) {
                    // Check if table exists first
                    if (!Schema::hasTable('student_information')) {
                        $this->warn("  [DRY] Table student_information does not exist - would need to create it first");
                        continue;
                    }
                    
                    $exists = Student::where('user_id', $user->id)->exists();
                    
                    if (!$exists) {
                        $this->line("  [DRY] Would create student_information for: {$user->email}");
                    } else {
                        $this->line("  [DRY] Would update student_information for: {$user->email}");
                    }
                } else {
                    // Check if table exists
                    if (!Schema::hasTable('student_information')) {
                        $this->warn("  ⚠ Table student_information does not exist - skipping {$user->email}");
                        continue;
                    }
                    
                    $this->syncUserToStudent($user);
                    $stats['role_1_synced']++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error syncing {$user->email}: " . $e->getMessage());
                $stats['errors']++;
            }
        }
        
        $this->newLine();
        
        // Sync Role 3 (assistant) to student_information AND student_leaders_information
        $this->info('Syncing Role 3 (assistant) to student_information and student_leaders_information...');
        $assistantUsers = User::where('role', 3)->get();
        
        foreach ($assistantUsers as $user) {
            try {
                if ($dryRun) {
                    // Check if tables exist
                    $studentTableExists = Schema::hasTable('student_information');
                    $leaderTableExists = Schema::hasTable('student_leaders_information');
                    
                    if (!$studentTableExists) {
                        $this->warn("  [DRY] Table student_information does not exist - would need to create it first");
                    } else {
                        $studentExists = Student::where('user_id', $user->id)->exists();
                        
                        if (!$studentExists) {
                            $this->line("  [DRY] Would create student_information for: {$user->email}");
                        } else {
                            $this->line("  [DRY] Would update student_information for: {$user->email}");
                        }
                    }
                    
                    if (!$leaderTableExists) {
                        $this->warn("  [DRY] Table student_leaders_information does not exist - would need to create it first");
                    } else {
                        $leaderExists = AssistantAssignment::where('user_id', $user->id)->exists();
                        
                        if (!$leaderExists) {
                            $this->line("  [DRY] Would create student_leaders_information for: {$user->email}");
                        } else {
                            $this->line("  [DRY] Would update student_leaders_information for: {$user->email}");
                        }
                    }
                } else {
                    // Check if tables exist
                    if (!Schema::hasTable('student_information')) {
                        $this->warn("  ⚠ Table student_information does not exist - skipping student sync for {$user->email}");
                    } else {
                        $this->syncUserToStudent($user);
                        $stats['role_3_synced_student']++;
                    }
                    
                    if (!Schema::hasTable('student_leaders_information')) {
                        $this->warn("  ⚠ Table student_leaders_information does not exist - skipping leader sync for {$user->email}");
                    } else {
                        $this->syncUserToStudentLeaders($user);
                        $stats['role_3_synced_leaders']++;
                    }
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error syncing {$user->email}: " . $e->getMessage());
                $stats['errors']++;
            }
        }
        
        $this->newLine();
        
        // Summary
        $this->info('=== Summary ===');
        if (!$dryRun) {
            $this->line("Role 2 (staff) synced: {$stats['role_2_synced']}");
            $this->line("Role 4 (admin) synced: {$stats['role_4_synced']}");
            $this->line("Role 1 (student) synced: {$stats['role_1_synced']}");
            $this->line("Role 3 (assistant) → student_information: {$stats['role_3_synced_student']}");
            $this->line("Role 3 (assistant) → student_leaders_information: {$stats['role_3_synced_leaders']}");
            if ($stats['errors'] > 0) {
                $this->error("Errors: {$stats['errors']}");
            }
        } else {
            $this->info('Run without --dry-run to perform actual sync');
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Sync user to staff_information (used by command, not observer)
     */
    private function syncUserToStaff(User $user)
    {
        $role = (int) $user->role;
        if ($role !== 2 && $role !== 4) {
            return;
        }
        
        $existingStaff = Staff::where('user_id', $user->id)
            ->orWhereRaw('LOWER(email) = ?', [strtolower(trim($user->email))])
            ->first();
        
        $staffData = [
            'first_name' => $user->first_name ?? '',
            'last_name' => $user->last_name ?? '',
            'middle_name' => $user->middle_name ?? '',
            'user_id' => $user->user_id,
            'email' => $user->email,
            'designation' => $user->designation ?? null,
            'department_id' => $user->department_id ?? null,
            'organization_id' => $user->organization_id ?? null,
            'contact_number' => $user->contact_number ?? null,
            'birth_date' => $user->birth_date ?? null,
            'gender' => $user->gender ?? null,
            'age' => $user->age ?? null,
        ];
        
        if (isset($user->image)) {
            $staffData['image'] = $user->image;
        }
        if (isset($user->service_order)) {
            $staffData['service_order'] = $user->service_order;
        }
        if (isset($user->length_of_service)) {
            $staffData['length_of_service'] = $user->length_of_service;
        }
        if (isset($user->contract_end_at)) {
            $staffData['contract_end_at'] = $user->contract_end_at;
        }
        if (isset($user->employment_status)) {
            $staffData['employment_status'] = $user->employment_status;
        }
        if (isset($user->about_me)) {
            $staffData['about_me'] = $user->about_me;
        }
        
        if ($existingStaff) {
            $existingStaff->update($staffData);
        } else {
            if ($role === 4) {
                $staffData['admin_id'] = $user->id;
            } else {
                $admin = User::where('role', 4)->first();
                $staffData['admin_id'] = $admin ? $admin->id : $user->id;
            }
            Staff::create($staffData);
        }
    }
    
    /**
     * Sync user to student_information (used by command, not observer)
     */
    private function syncUserToStudent(User $user)
    {
        $role = (int) $user->role;
        if ($role !== 1 && $role !== 3) {
            return;
        }
        
        $existingStudent = Student::where('user_id', $user->id)->first();
        $studentInfo = $user->studentInformation;
        
        $studentData = [
            'user_id' => $user->id,
            'first_name' => $user->first_name ?? '',
            'middle_name' => $user->middle_name ?? '',
            'last_name' => $user->last_name ?? '',
            'email' => $user->email ?? null,
            'contact_number' => $user->contact_number ?? '',
            'gender' => $user->gender ?? 'other',
            'birth_date' => $user->birth_date ?? null,
            'department_id' => $user->department_id ?? null,
            'course_id' => $user->course_id ?? null,
            'organization_id' => $user->organization_id ?? null,
            'scholarship_id' => optional($studentInfo)->scholarship_id ?? $user->scholarship_id ?? null,
            'year_level' => optional($studentInfo)->year_level ?? null,
            'student_type1' => optional($studentInfo)->student_type1 ?? null,
            'student_type2' => optional($studentInfo)->student_type2 ?? null,
        ];
        
        if (isset($user->ext_name)) {
            $studentData['ext_name'] = $user->ext_name;
        }
        if (isset($user->tel_no)) {
            $studentData['tel_no'] = $user->tel_no;
        }
        if (isset($user->age)) {
            $studentData['age'] = $user->age;
        }
        if (isset($user->image)) {
            $studentData['personal_data_sheet_image'] = $user->image;
        }
        
        if ($existingStudent) {
            $existingStudent->update($studentData);
        } else {
            Student::create($studentData);
        }
    }
    
    /**
     * Sync user to student_leaders_information (used by command, not observer)
     */
    private function syncUserToStudentLeaders(User $user)
    {
        $role = (int) $user->role;
        if ($role !== 3) {
            return;
        }
        
        $existingAssignment = AssistantAssignment::where('user_id', $user->id)->first();
        
        $assignmentData = [
            'user_id' => $user->id,
            'organization_id' => $user->organization_id ?? null,
            'department_id' => $user->department_id ?? null,
            'supervisor_id' => $user->supervisor_id ?? null,
            'active' => true,
        ];
        
        if (isset($user->position)) {
            $assignmentData['position'] = $user->position;
        }
        
        if ($existingAssignment) {
            $existingAssignment->update($assignmentData);
        } else {
            AssistantAssignment::create($assignmentData);
        }
    }
}
