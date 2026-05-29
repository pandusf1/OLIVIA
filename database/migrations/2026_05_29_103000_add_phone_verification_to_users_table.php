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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone_is_verified')) {
                $table->boolean('phone_is_verified')->default(true)->after('phone');
            }

            if (!Schema::hasColumn('users', 'phone_verification_code')) {
                $table->string('phone_verification_code', 10)->nullable()->after('phone_is_verified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'phone_is_verified') ? 'phone_is_verified' : null,
                Schema::hasColumn('users', 'phone_verification_code') ? 'phone_verification_code' : null,
            ]);

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
