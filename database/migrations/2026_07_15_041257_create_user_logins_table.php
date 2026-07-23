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
        Schema::create('userlogin', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('login_name')->unique();
            $table->string('password');
            $table->enum('is_active', ['0', '1'])->default('0');
            $table->enum('is_deleted', ['0', '1'])->default('0');
            $table->date('deleted_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('userlogin ');
    }
};
