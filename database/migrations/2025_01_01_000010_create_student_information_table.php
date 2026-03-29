<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('year_level')->nullable();
            $table->string('student_type1')->nullable();
            $table->string('student_type2')->nullable();
            $table->string('student_type')->nullable();
            $table->string('school_year')->nullable();
            $table->string('semester')->nullable();
            $table->string('academic_year')->nullable();
            $table->foreignId('scholarship_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active_scholar')->default(false);
            $table->string('scholarship_grant_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_information');
    }
};

