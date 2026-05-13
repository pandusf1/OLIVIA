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
        Schema::table('reports', function (Blueprint $table) {

            $table->uuid('partner_id')->nullable()->after('user_id');

            $table->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {

            $table->dropForeign(['partner_id']);
            $table->dropColumn('partner_id');

        });
    }
};