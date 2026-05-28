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
        Schema::table('trusted_contacts', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false);
            $table->string('verification_code', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trusted_contacts', function (Blueprint $table) {
            $table->dropColumn(['is_verified', 'verification_code']);
        });
    }
};
