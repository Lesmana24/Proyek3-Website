<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom-kolom hasil diagnosa AI ke tabel plant_health_scans.
     */
    public function up(): void
    {
        Schema::table('plant_health_scans', function (Blueprint $table) {
            $table->string('plant_name')->nullable()->after('confidence_score');
            $table->string('care_light')->nullable()->after('plant_name');
            $table->string('care_water')->nullable()->after('care_light');
            $table->string('care_temperature')->nullable()->after('care_water');
            $table->json('problems_list')->nullable()->after('care_temperature');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plant_health_scans', function (Blueprint $table) {
            $table->dropColumn([
                'plant_name',
                'care_light',
                'care_water',
                'care_temperature',
                'problems_list',
            ]);
        });
    }
};
