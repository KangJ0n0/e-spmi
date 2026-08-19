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
        Schema::create('bank_pertanyaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('pertanyaan');
            $table->text('butir_pertanyaan');
            $table->text('dokumen_cek');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_pertanyaans');
    }
};
