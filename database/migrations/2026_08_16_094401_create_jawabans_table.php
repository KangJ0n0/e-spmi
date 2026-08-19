<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jawabans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('pertanyaan_id'); // FK ke bank_pertanyaans atau list_pertanyaans
            
            // Status temuan dari klik tombol (KS / KTS)
            $table->enum('status_temuan', ['KS', 'KTS'])->nullable();
            
            // Tahap 1
            $table->text('deskripsi_hasil')->nullable();
            
            // Cabang KS (Kondisi Sesuai)
            $table->text('faktor_pendukung')->nullable();
            $table->text('rencana_peningkatan')->nullable();
            
            // Cabang KTS (Kondisi Tidak Sesuai)
            $table->enum('kategori_temuan', ['OBS', 'MINOR', 'MAYOR'])->nullable();
            $table->text('faktor_penghambat')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->text('rencana_perbaikan')->nullable();
            $table->text('jadwal_penyelesaian')->nullable();
            $table->text('pihak_tanggung_jawab')->nullable();
            
            $table->timestamps();

            // Foreign key ke tabel pertanyaan (sesuaikan dengan nama tabel yang Anda gunakan)
            // $table->foreign('pertanyaan_id')->references('id')->on('bank_pertanyaans')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jawabans');
    }
};