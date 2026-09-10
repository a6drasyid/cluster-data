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
        Schema::table('demographic_data', function (Blueprint $table) {
            $table->string('dusun')->nullable()->after('district');
            $table->double('employment_status')->nullable()->after('age'); // % Bekerja / Angkatan Kerja
            $table->double('family_size')->nullable()->after('employment_status'); // Rerata Anggota Keluarga (Jiwa/KK)
            $table->double('home_ownership')->nullable()->after('family_size'); // % Kepemilikan Rumah Sendiri
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demographic_data', function (Blueprint $table) {
            $table->dropColumn(['dusun', 'employment_status', 'family_size', 'home_ownership']);
        });
    }
};
