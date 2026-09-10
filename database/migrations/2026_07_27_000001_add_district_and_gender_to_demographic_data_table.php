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
            $table->string('district')->nullable()->after('region_name');
            $table->double('male_population')->nullable()->after('year');
            $table->double('female_population')->nullable()->after('male_population');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demographic_data', function (Blueprint $table) {
            $table->dropColumn(['district', 'male_population', 'female_population']);
        });
    }
};
