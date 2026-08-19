<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('list_pertanyaans', function (Blueprint $table) {
            // Menambahkan kolom status_jawaban dengan default 'belum'
            $table->enum('status_jawaban', ['belum', 'sudah'])->default('belum')->after('pertanyaan_id');
        });
    }

    public function down()
    {
        Schema::table('list_pertanyaans', function (Blueprint $table) {
            $table->dropColumn('status_jawaban');
        });
    }
};