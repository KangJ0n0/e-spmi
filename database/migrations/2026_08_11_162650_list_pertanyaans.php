<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('list_pertanyaans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('jadwal_id');      // FK mengarah ke tabel jadwal_spmi
            $table->uuid('pertanyaan_id');  // FK mengarah ke tabel bank_pertanyaans
            $table->timestamps();

            // Foreign Key ke tabel jadwal_spmi
            $table->foreign('jadwal_id')
                  ->references('id')
                  ->on('jadwal_spmi')
                  ->onDelete('cascade');

            // Foreign Key ke tabel bank_pertanyaans
            $table->foreign('pertanyaan_id')
                  ->references('id')
                  ->on('bank_pertanyaans')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('list_pertanyaans');
    }
};