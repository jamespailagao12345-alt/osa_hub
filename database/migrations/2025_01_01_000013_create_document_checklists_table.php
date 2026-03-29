<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('form_137_presented')->default(false);
            $table->boolean('tor_presented')->default(false);
            $table->boolean('good_moral_cert_presented')->default(false);
            $table->boolean('birth_cert_presented')->default(false);
            $table->boolean('marriage_cert_presented')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_checklists');
    }
};

