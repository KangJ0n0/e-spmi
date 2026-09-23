<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bank_pertanyaans', 'urutan')) {
            Schema::table('bank_pertanyaans', function (Blueprint $table) {
                $table->unsignedBigInteger('urutan')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bank_pertanyaans', 'urutan')) {
            Schema::table('bank_pertanyaans', function (Blueprint $table) {
                $table->dropColumn('urutan');
            });
        }
    }
};