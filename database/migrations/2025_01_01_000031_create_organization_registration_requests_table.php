<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_registration_requests', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('acronym')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('proposed_by')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->text('decline_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('proposed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_registration_requests');
    }
};

