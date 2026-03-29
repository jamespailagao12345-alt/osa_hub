<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('staff_information')->onDelete('cascade');
            $table->string('role')->nullable();
            $table->timestamps();
            
            $table->unique(['organization_id', 'staff_id']);
            $table->index('organization_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_staff');
    }
};

