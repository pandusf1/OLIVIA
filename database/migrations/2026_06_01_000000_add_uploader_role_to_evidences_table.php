<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            if (!Schema::hasColumn('evidences', 'uploader_role')) {
                $table->string('uploader_role')->default('Saksi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evidences', function (Blueprint $table) {
            $table->dropColumn('uploader_role');
        });
    }
};
