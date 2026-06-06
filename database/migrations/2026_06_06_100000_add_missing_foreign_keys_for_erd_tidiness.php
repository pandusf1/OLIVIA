<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sanitize audit_logs orphan records: if there are any user_ids that do not exist in users table, set them to NULL
        DB::table('audit_logs')
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('users')
                      ->whereColumn('users.id', 'audit_logs.user_id');
            })
            ->update(['user_id' => null]);

        // 2. Add foreign keys to reports
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('routed_partner_id')
                ->references('id')
                ->on('partners')
                ->nullOnDelete();
        });

        // 3. Add foreign key to trusted_contacts
        Schema::table('trusted_contacts', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });

        // 4. Add foreign key to audit_logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('trusted_contacts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['routed_partner_id']);
        });
    }
};
