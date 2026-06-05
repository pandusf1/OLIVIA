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
        Schema::table('chat_threads', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->change();
            $table->uuid('partner_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('chat_threads', function (Blueprint $table) {
            $table->uuid('user_id')->nullable(false)->change();
            $table->uuid('partner_id')->nullable(false)->change();
        });
    }
};
