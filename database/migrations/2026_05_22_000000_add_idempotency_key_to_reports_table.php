<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('idempotency_key')->nullable()->after('anonymous');
            $table->index('idempotency_key', 'reports_idempotency_key_idx');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_idempotency_key_idx');
            $table->dropColumn('idempotency_key');
        });
    }
};

