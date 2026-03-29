<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique()->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('ext_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('tel_no')->nullable();
            $table->string('image')->nullable();
            $table->integer('role')->default(1); // 1=Student, 2=Staff, 3=Student Leader, 4=Admin
            $table->string('position')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('scholarship_id')->nullable()->constrained()->onDelete('set null');
            $table->string('designation')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('suspended')->default(false);
            $table->text('suspension_reason')->nullable();
            $table->text('about_me')->nullable();
            $table->string('last_imported_worksheet')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('role');
            $table->index('email');
            $table->index('department_id');
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

