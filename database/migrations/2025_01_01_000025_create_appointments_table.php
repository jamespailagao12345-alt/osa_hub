<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('full_name');
            $table->string('email');
            $table->string('contact_number')->nullable();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->text('concern')->nullable();
            $table->text('message')->nullable();
            $table->foreignId('assigned_staff_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['pending', 'approved', 'declined', 'cancelled', 'rescheduled', 'completed'])->default('pending');
            $table->string('session')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('action_reason')->nullable();
            $table->date('rescheduled_date')->nullable();
            $table->time('rescheduled_time')->nullable();
            $table->text('reason_for_counseling')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamp('rescheduled_reminder_sent_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index('appointment_date');
            $table->index('status');
            $table->index('user_id');
            $table->index('assigned_staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

