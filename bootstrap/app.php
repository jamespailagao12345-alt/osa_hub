<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'designation' => \App\Http\Middleware\CheckStaffDesignation::class,
            'staff.participants.guard' => \App\Http\Middleware\StaffParticipantsGuard::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        // Send appointment reminders every minute
        $schedule->command('appointments:send-reminders')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();
        
        // Mark absent participants every 5 minutes
        $schedule->command('events:mark-absent-participants')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();
        
        // Check for consecutive absences and lateness every hour
        $schedule->command('events:check-consecutive-absences-lateness')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
