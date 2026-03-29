<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class VerifyStudentUserIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:verify-integrity {--fix : Attempt to fix issues found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that all students in students table have corresponding users in users table, and that QR codes are generated from users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Student-User Data Integrity Verification ===');
        $this->newLine();

        $fix = $this->option('fix');
        
        if ($fix) {
            $this->warn('Running in FIX mode. This will attempt to fix issues found.');
            $this->newLine();
        }

        $issues = [];
        $warnings = [];

        // 1. Check students without corresponding users
        $this->info('Step 1: Checking students without corresponding users...');
        $studentsWithoutUsers = DB::table('student_information')
            ->leftJoin('users', 'student_information.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->select('student_information.*')
            ->get();

        if ($studentsWithoutUsers->count() > 0) {
            $this->error("Found {$studentsWithoutUsers->count()} student(s) without corresponding user records:");
            foreach ($studentsWithoutUsers as $student) {
                $this->line("  - Student ID: {$student->id}, Email: {$student->email}, Name: {$student->first_name} {$student->last_name}");
                $issues[] = [
                    'type' => 'missing_user',
                    'student_id' => $student->id,
                    'email' => $student->email,
                    'name' => "{$student->first_name} {$student->last_name}",
                ];
            }
            
            if ($fix) {
                $this->info('Attempting to fix: Creating missing user records...');
                foreach ($studentsWithoutUsers as $student) {
                    try {
                        // Get student as Eloquent model to access relationships
                        $studentModel = Student::find($student->id);
                        if (!$studentModel) {
                            $this->error("  ✗ Student record not found: ID {$student->id}");
                            continue;
                        }
                        
                        // Check if this student already has a user_id that points to a non-existent user
                        $currentUserId = $studentModel->user_id;
                        $existingUser = null;
                        
                        if ($currentUserId) {
                            // Check if user exists
                            $existingUser = User::find($currentUserId);
                            
                            // If user doesn't exist, check if there's another student using this user_id
                            if (!$existingUser) {
                                $duplicateStudent = DB::table('student_information')
                                    ->where('user_id', $currentUserId)
                                    ->where('id', '!=', $student->id)
                                    ->first();
                                
                                if ($duplicateStudent) {
                                    $this->warn("  ! Student {$student->email} has invalid user_id {$currentUserId} (already used by another student). Checking for user by email...");
                                }
                            }
                        }
                        
                        // Try to find user by email
                        if (!$existingUser) {
                            $existingUser = User::where('email', $student->email)->first();
                        }
                        
                        if ($existingUser) {
                            // Check if another student already uses this user_id
                                $conflictStudent = DB::table('student_information')
                                ->where('user_id', $existingUser->id)
                                ->where('id', '!=', $studentModel->id)
                                ->first();
                            
                            if ($conflictStudent) {
                                $this->error("  ✗ Cannot link: User {$existingUser->id} already linked to another student (ID: {$conflictStudent->id})");
                                $this->warn("  ! Consider merging or deleting duplicate student record {$studentModel->id}");
                            } else {
                                // Link student to existing user
                                $studentModel->user_id = $existingUser->id;
                                $studentModel->save();
                                $this->info("  ✓ Linked student {$studentModel->email} to existing user {$existingUser->id}");
                            }
                        } else {
                            // Create new user
                            $user = User::create([
                                'user_id' => $studentModel->student_id ?? 'AUTO_' . $studentModel->id . '_' . time(),
                                'first_name' => $studentModel->first_name,
                                'middle_name' => $studentModel->middle_name,
                                'last_name' => $studentModel->last_name,
                                'email' => $studentModel->email,
                                'gender' => $studentModel->gender ?? 'other',
                                'birth_date' => $studentModel->birth_date,
                                'department_id' => $studentModel->department_id,
                                'course_id' => $studentModel->course_id,
                                'organization_id' => $studentModel->organization_id,
                                'year_level' => $studentModel->year_level,
                                'student_type1' => $studentModel->student_type1,
                                'student_type2' => $studentModel->student_type2,
                                'scholarship_id' => $studentModel->scholarship_id,
                                'contact_number' => $studentModel->contact_number,
                                'emergency_contact_name' => $studentModel->emergency_contact_name,
                                'emergency_contact_number' => $studentModel->emergency_contact_number,
                                'emergency_relation' => $studentModel->emergency_relation,
                                'role' => 1,
                                'password' => bcrypt('temp_password_' . $studentModel->email),
                            ]);
                            
                            // Link student to new user
                            $studentModel->user_id = $user->id;
                            $studentModel->save();
                            $this->info("  ✓ Created user and linked student {$studentModel->email} (User ID: {$user->id})");
                        }
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to fix student {$student->email}: " . $e->getMessage());
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                            $this->warn("  ! This appears to be a duplicate user_id constraint violation. Manual intervention may be required.");
                        }
                    }
                }
            }
        } else {
            $this->info('  ✓ All students have corresponding user records.');
        }

        $this->newLine();

        // 2. Check users (role=1) without corresponding student records
        $this->info('Step 2: Checking users (students) without corresponding student records...');
        $usersWithoutStudents = User::where('role', 1)
            ->whereDoesntHave('student')
            ->get();

        if ($usersWithoutStudents->count() > 0) {
            $this->warn("Found {$usersWithoutStudents->count()} user(s) without corresponding student records:");
            foreach ($usersWithoutStudents as $user) {
                $this->line("  - User ID: {$user->id}, Email: {$user->email}, Name: {$user->first_name} {$user->last_name}");
                $warnings[] = [
                    'type' => 'missing_student',
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => "{$user->first_name} {$user->last_name}",
                ];
            }
            
            if ($fix) {
                $this->info('Attempting to fix: Creating missing student records...');
                foreach ($usersWithoutStudents as $user) {
                    try {
                        Student::create([
                            'user_id' => $user->id,
                            'first_name' => $user->first_name,
                            'middle_name' => $user->middle_name,
                            'last_name' => $user->last_name,
                            'email' => $user->email,
                            'gender' => $user->gender,
                            'birth_date' => $user->birth_date,
                            'department_id' => $user->department_id,
                            'course_id' => $user->course_id,
                            'organization_id' => $user->organization_id,
                            'year_level' => $user->year_level,
                            'student_type1' => $user->student_type1,
                            'student_type2' => $user->student_type2,
                            'scholarship_id' => $user->scholarship_id,
                            'contact_number' => $user->contact_number,
                            'emergency_contact_name' => $user->emergency_contact_name,
                            'emergency_contact_number' => $user->emergency_contact_number,
                            'emergency_relation' => $user->emergency_relation,
                        ]);
                        $this->info("  ✓ Created student record for user {$user->email}");
                    } catch (\Exception $e) {
                        $this->error("  ✗ Failed to create student for user {$user->email}: " . $e->getMessage());
                    }
                }
            }
        } else {
            $this->info('  ✓ All users (students) have corresponding student records.');
        }

        $this->newLine();

        // 3. Verify foreign key constraint
        $this->info('Step 3: Verifying foreign key constraint...');
        try {
            $invalidForeignKeys = DB::select("
                SELECT s.id, s.user_id, s.email, s.first_name, s.last_name
                FROM students s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE u.id IS NULL AND s.user_id IS NOT NULL
            ");

            if (count($invalidForeignKeys) > 0) {
                $this->error("Found " . count($invalidForeignKeys) . " student(s) with invalid foreign keys:");
                foreach ($invalidForeignKeys as $row) {
                    $this->line("  - Student ID: {$row->id}, User ID: {$row->user_id}, Email: {$row->email}");
                    $issues[] = [
                        'type' => 'invalid_foreign_key',
                        'student_id' => $row->id,
                        'user_id' => $row->user_id,
                        'email' => $row->email,
                    ];
                }
            } else {
                $this->info('  ✓ All foreign keys are valid.');
            }
        } catch (\Exception $e) {
            $this->warn("  Could not verify foreign keys: " . $e->getMessage());
        }

        $this->newLine();

        // 4. Verify QR code generation uses users table
        $this->info('Step 4: Verifying QR code generation source...');
        $this->info('  Checking QR code generation commands and controllers...');
        
        $qrGenerationFiles = [
            'app/Console/Commands/GenerateStudentQRCodes.php',
            'app/Http/Controllers/Student/DashboardController.php',
            'app/Http/Controllers/RegisteredUserController.php',
        ];
        
        $allUseUsersTable = true;
        foreach ($qrGenerationFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                // Check if it uses User model or users table
                if (preg_match('/User::|users\.|from.*users/i', $content)) {
                    $this->info("  ✓ {$file} uses users table");
                } elseif (preg_match('/Student::|students\./i', $content) && !preg_match('/User::|users\./i', $content)) {
                    $this->error("  ✗ {$file} may not be using users table correctly");
                    $allUseUsersTable = false;
                }
            }
        }
        
        if ($allUseUsersTable) {
            $this->info('  ✓ All QR code generation appears to use users table.');
        } else {
            $warnings[] = [
                'type' => 'qr_generation_source',
                'message' => 'Some QR code generation may not be using users table correctly',
            ];
        }

        $this->newLine();

        // 5. Check student creation order (students should be created AFTER users)
        $this->info('Step 5: Checking student creation timestamps vs user creation timestamps...');
        $studentsCreatedBeforeUsers = DB::select("
            SELECT s.id, s.user_id, s.email, s.created_at as student_created, u.created_at as user_created
            FROM students s
            INNER JOIN users u ON s.user_id = u.id
            WHERE s.created_at < u.created_at
        ");

        if (count($studentsCreatedBeforeUsers) > 0) {
            $this->warn("Found " . count($studentsCreatedBeforeUsers) . " student(s) created before their user records:");
            foreach ($studentsCreatedBeforeUsers as $row) {
                $this->line("  - Student ID: {$row->id}, Email: {$row->email}");
                $this->line("    Student created: {$row->student_created}, User created: {$row->user_created}");
                $warnings[] = [
                    'type' => 'creation_order',
                    'student_id' => $row->id,
                    'email' => $row->email,
                    'student_created' => $row->student_created,
                    'user_created' => $row->user_created,
                ];
            }
        } else {
            $this->info('  ✓ All students were created after their user records.');
        }

        $this->newLine();

        // Summary
        $this->info('=== Summary ===');
        $this->table(
            ['Category', 'Count'],
            [
                ['Critical Issues', count($issues)],
                ['Warnings', count($warnings)],
            ]
        );

        if (count($issues) > 0 || count($warnings) > 0) {
            $this->newLine();
            if (!$fix) {
                $this->comment('Run with --fix option to attempt automatic fixes.');
            }
            return Command::FAILURE;
        }

        $this->info('✓ All integrity checks passed!');
        return Command::SUCCESS;
    }
}
