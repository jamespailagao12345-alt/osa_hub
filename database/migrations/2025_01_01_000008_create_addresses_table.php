<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('addresses')) {
            return;
        }
        
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable'); // addressable_type, addressable_id
            $table->enum('type', ['home', 'work', 'school', 'emergency', 'parent_guardian']);
            $table->text('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city_municipality')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code')->nullable();
            $table->text('complete_address')->nullable();
            $table->timestamps();
            
            $table->index(['addressable_type', 'addressable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};

