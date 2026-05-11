<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->text('file_hash')->nullable();
            $table->text('uploaded_ip')->nullable();
            $table->text('device_info')->nullable();
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