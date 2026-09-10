<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('demographic_data');

        Schema::create('demographic_data', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->nullable();
            $table->string('nama')->nullable();
            $table->string('jenis_kelamin', 1)->nullable(); // L / P
            $table->integer('usia')->nullable();
            $table->string('status_perkawinan')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('desa')->nullable();
            $table->string('dusun')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->integer('jumlah_anggota_keluarga')->nullable();
            $table->decimal('pendapatan_per_bulan', 15, 2)->nullable();
            $table->string('kepemilikan_rumah')->nullable();
            $table->string('penerima_pkh', 10)->nullable(); // Ya / Tidak
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->integer('cluster')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demographic_data');

        Schema::create('demographic_data', function (Blueprint $table) {
            $table->id();
            $table->string('region_name')->nullable();
            $table->string('district')->nullable();
            $table->string('dusun')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->integer('year')->nullable();
            $table->integer('male_population')->nullable();
            $table->integer('female_population')->nullable();
            $table->decimal('population_density', 10, 1)->nullable();
            $table->string('education_level')->nullable();
            $table->decimal('income', 15, 2)->nullable();
            $table->decimal('age', 5, 1)->nullable();
            $table->decimal('employment_status', 5, 1)->nullable();
            $table->decimal('family_size', 5, 1)->nullable();
            $table->decimal('home_ownership', 5, 1)->nullable();
            $table->integer('cluster')->nullable();
            $table->timestamps();
        });
    }
};
