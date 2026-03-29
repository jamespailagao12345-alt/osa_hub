<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateStudentQRCodes extends Command
{
    protected $signature = 'students:generate-qrcodes {--dry-run : Show what would be generated without creating files}';
    protected $description = 'Generate QR codes for all students (role=1) that are missing QR codes';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Checking QR codes for all students (role=1)...');
        $this->newLine();

        // Get all students
        $students = User::where('role', 1)
            ->with(['department', 'course'])
            ->get();

        $total = $students->count();
        $missing = 0;
        $generated = 0;
        $errors = 0;
        $skipped = 0;

        $this->info("Found {$total} students.");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($students as $student) {
            try {
                $qrPath = "qr-codes/{$student->id}.svg";
                
                // Check if QR code already exists
                if (Storage::disk('public')->exists($qrPath)) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                $missing++;

                if ($dryRun) {
                    $this->newLine();
                    $this->line("[DRY RUN] Would generate QR code for: {$student->email} (ID: {$student->id})");
                    $progressBar->advance();
                    continue;
                }

                // Generate QR code payload
                $qrPayload = [
                    'student_id' => $student->id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name ?? null,
                    'last_name' => $student->last_name,
                    'department' => optional($student->department)->name,
                    'course' => optional($student->course)->name,
                    'year_level' => $student->year_level,
                    'generated_at' => now()->toIso8601String(),
                ];

                $qrData = json_encode($qrPayload);
                
                // Generate SVG QR code
                $svg = QrCode::format('svg')->size(300)->generate($qrData);
                
                // Store QR code
                Storage::disk('public')->put($qrPath, $svg);
                
                $generated++;
                $progressBar->advance();
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("✗ Failed to generate QR code for {$student->email} (ID: {$student->id}): " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Total Students', $total],
                ['Already Have QR Code', $skipped],
                ['Missing QR Code', $missing],
                ['Generated', $generated],
                ['Errors', $errors],
            ]
        );

        if ($dryRun && $missing > 0) {
            $this->newLine();
            $this->comment("Run without --dry-run to generate {$missing} QR code(s).");
        }

        return Command::SUCCESS;
    }
}

