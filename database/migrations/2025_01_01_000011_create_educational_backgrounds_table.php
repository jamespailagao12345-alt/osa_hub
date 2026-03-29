<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educational_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('level', ['elementary', 'junior_high', 'senior_high', 'college', 'last_school']);
            $table->string('school_name')->nullable();
            $table->text('address')->nullable();
            $table->string('year_graduated')->nullable();
            $table->string('year_completed')->nullable();
            $table->string('course')->nullable();
            $table->string('track_strand')->nullable();
            $table->string('lrn')->nullable();
            $table->text('honors_awards')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educational_backgrounds');
    }
};

