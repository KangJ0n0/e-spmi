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
        Schema::create('jadwal_spmi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->string('semester', 5);
            $table->text('nama_jadwal');
            $table->text('area_audit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_spmi');
    }
};
