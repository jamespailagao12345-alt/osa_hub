<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('relation', ['father', 'mother', 'guardian', 'spouse']);
            $table->string('name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('occupation')->nullable();
            $table->string('workplace')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->string('relationship')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('relation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};

