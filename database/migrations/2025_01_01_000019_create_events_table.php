<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('event_date');
            $table->date('end_date')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
            $table->string('coordinator_name')->nullable();
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->text('decline_reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->string('qr_code_path')->nullable();
            $table->boolean('required_student_participation')->default(false);
            $table->boolean('monitoring_started')->default(false);
            $table->dateTime('monitoring_started_at')->nullable();
            $table->integer('attended_threshold_minutes')->nullable();
            $table->integer('late_threshold_minutes')->nullable();
            $table->integer('absent_threshold_minutes')->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
            
            $table->index('event_date');
            $table->index('status');
            $table->index('created_by');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

