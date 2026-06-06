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
        Schema::table('partners', function (Blueprint $table) {
            $table->text('catatan')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('nomor_rekening')->nullable();
            $table->string('ewallet_name')->nullable();
            $table->string('nomor_ewallet')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('partners', function (Blueprint $table) {
            $table->dropColumn(['catatan', 'bank_name', 'nomor_rekening', 'ewallet_name', 'nomor_ewallet']);
        });
    }
};
