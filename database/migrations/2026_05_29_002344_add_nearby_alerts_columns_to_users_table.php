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
            $table->boolean('receive_nearby_alerts')->default(true);
            $table->integer('nearby_alert_count')->default(0);
            $table->integer('next_nearby_alert_threshold')->default(5);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'receive_nearby_alerts',
                'nearby_alert_count',
                'next_nearby_alert_threshold',
            ]);
        });
    }
};
