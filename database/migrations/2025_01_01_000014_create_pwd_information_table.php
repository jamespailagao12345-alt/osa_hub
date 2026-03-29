<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pwd_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->string('pwd_id_number')->nullable();
            $table->string('pwd_id_image')->nullable();
            $table->string('disability_type')->nullable();
            $table->text('disability_description')->nullable();
            $table->date('pwd_id_issued_date')->nullable();
            $table->date('pwd_id_expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pwd_information');
    }
};

