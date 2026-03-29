<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_information', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->onDelete('set null');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('designation')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('contact_number')->nullable();
            $table->string('image')->nullable();
            $table->string('service_order')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->integer('age')->nullable();
            $table->string('length_of_service')->nullable();
            $table->date('contract_end_at')->nullable();
            $table->enum('employment_status', ['active', 'inactive', 'ended'])->default('active');
            $table->text('about_me')->nullable();
            $table->timestamps();
            
            $table->index('email');
            $table->index('department_id');
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_information');
    }
};

