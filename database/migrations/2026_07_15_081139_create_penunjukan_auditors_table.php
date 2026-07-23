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
        Schema::create('penunjukan_auditors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('dosen_id')->index();
            $table->enum('status', ['auditee', 'auditor']);
            $table->string('semester', 5);
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penunjukan_auditors');
    }
};
