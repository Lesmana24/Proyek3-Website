<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plant_health_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('pengguna')
                  ->nullOnDelete();
            $table->string('image_path');
            $table->string('ai_health_status');
            $table->string('disease_name')->nullable();
            $table->decimal('confidence_score', 8, 2); // Digunakan untuk menyimpan skor akurasi (contoh: 98.50)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_health_scans');
    }
};
