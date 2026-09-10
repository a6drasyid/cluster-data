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
        Schema::create('demographic_data', function (Blueprint $table) {
            $table->id();
            $table->string('region_name');
            $table->string('province');
            $table->double('latitude');
            $table->double('longitude');
            $table->integer('year');
            $table->double('population_density');
            $table->double('education_level');
            $table->double('income');
            $table->double('age');
            $table->integer('cluster')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demographic_data');
    }
};
