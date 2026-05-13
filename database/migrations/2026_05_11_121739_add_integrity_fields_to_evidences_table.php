<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            if (!Schema::hasColumn('evidences', 'file_hash')) {
                $table->text('file_hash')->nullable();
            }

            if (!Schema::hasColumn('evidences', 'uploaded_ip')) {
                $table->text('uploaded_ip')->nullable();
            }

            if (!Schema::hasColumn('evidences', 'device_info')) {
                $table->text('device_info')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->dropColumn([
                'file_hash',
                'uploaded_ip',
                'device_info'
            ]);
        });
    }
};