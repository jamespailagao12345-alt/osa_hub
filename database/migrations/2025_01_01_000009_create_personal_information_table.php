<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_information', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('age')->nullable();
            $table->string('civil_status')->nullable();
            $table->string('maiden_name')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->foreignId('nationality_id')->nullable()->constrained()->onDelete('set null');
            $table->string('nationality')->nullable(); // Legacy field
            $table->string('religion')->nullable();
            $table->string('sport')->nullable();
            $table->string('arts')->nullable();
            $table->string('technical')->nullable();
            $table->boolean('is_indigenous_group_member')->default(false);
            $table->string('indigenous_group_specify')->nullable();
            $table->boolean('is_pwd')->default(false);
            $table->string('pwd_id_image')->nullable();
            $table->boolean('is_government_member')->default(false);
            $table->string('government_level')->nullable();
            $table->string('government_role_position')->nullable();
            $table->string('living_arrangement')->nullable();
            $table->string('living_arrangement_others_specify')->nullable();
            $table->boolean('is_single_parent')->default(false);
            $table->string('fraternity_sorority_name')->nullable();
            $table->string('fraternity_sorority_position')->nullable();
            $table->boolean('has_criminal_record')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_information');
    }
};

