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
        $isSqlite = DB::getDriverName() === 'sqlite';

        // 1. Drop foreign keys if not SQLite
        if (!$isSqlite) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropForeign(['routed_partner_id']);
            });

            Schema::table('price_lists', function (Blueprint $table) {
                $table->dropForeign(['partner_id']);
            });

            Schema::table('chat_threads', function (Blueprint $table) {
                $table->dropForeign(['partner_id']);
            });

            Schema::table('report_partner_routings', function (Blueprint $table) {
                $table->dropForeign(['partner_id']);
            });

            Schema::table('user_partner_payments', function (Blueprint $table) {
                $table->dropForeign(['partner_id']);
            });
        }

        // 2. Rename tables
        Schema::rename('partners', 'mitras');
        Schema::rename('report_partner_routings', 'report_mitra_routings');
        Schema::rename('user_partner_payments', 'user_mitra_payments');

        // 3. Rename columns in tables
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'mitra_id');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->renameColumn('routed_partner_id', 'routed_mitra_id');
            $table->renameColumn('handler_partner_id', 'handler_mitra_id');
        });

        Schema::table('price_lists', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'mitra_id');
        });

        Schema::table('chat_threads', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'mitra_id');
        });

        Schema::table('report_mitra_routings', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'mitra_id');
        });

        Schema::table('user_mitra_payments', function (Blueprint $table) {
            $table->renameColumn('partner_id', 'mitra_id');
        });

        Schema::table('mitras', function (Blueprint $table) {
            $table->renameColumn('partner_name', 'mitra_name');
            $table->renameColumn('partner_type', 'mitra_type');
        });

        // 4. Re-add foreign keys
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('routed_mitra_id')
                ->references('id')
                ->on('mitras')
                ->nullOnDelete();
        });

        Schema::table('price_lists', function (Blueprint $table) {
            $table->foreign('mitra_id')
                ->references('id')
                ->on('mitras')
                ->cascadeOnDelete();
        });

        Schema::table('chat_threads', function (Blueprint $table) {
            $table->foreign('mitra_id')
                ->references('id')
                ->on('mitras')
                ->cascadeOnDelete();
        });

        Schema::table('report_mitra_routings', function (Blueprint $table) {
            $table->foreign('mitra_id')
                ->references('id')
                ->on('mitras')
                ->cascadeOnDelete();
        });

        Schema::table('user_mitra_payments', function (Blueprint $table) {
            $table->foreign('mitra_id')
                ->references('id')
                ->on('mitras')
                ->cascadeOnDelete();
        });

        // 5. Update user role values
        DB::table('users')->where('role', 'partner')->update(['role' => 'mitra']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // 1. Drop foreign keys if not SQLite
        if (!$isSqlite) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropForeign(['routed_mitra_id']);
            });

            Schema::table('price_lists', function (Blueprint $table) {
                $table->dropForeign(['mitra_id']);
            });

            Schema::table('chat_threads', function (Blueprint $table) {
                $table->dropForeign(['mitra_id']);
            });

            Schema::table('report_mitra_routings', function (Blueprint $table) {
                $table->dropForeign(['mitra_id']);
            });

            Schema::table('user_mitra_payments', function (Blueprint $table) {
                $table->dropForeign(['mitra_id']);
            });
        }

        // 2. Rename columns back
        Schema::table('mitras', function (Blueprint $table) {
            $table->renameColumn('mitra_name', 'partner_name');
            $table->renameColumn('mitra_type', 'partner_type');
        });

        Schema::table('user_mitra_payments', function (Blueprint $table) {
            $table->renameColumn('mitra_id', 'partner_id');
        });

        Schema::table('report_mitra_routings', function (Blueprint $table) {
            $table->renameColumn('mitra_id', 'partner_id');
        });

        Schema::table('chat_threads', function (Blueprint $table) {
            $table->renameColumn('mitra_id', 'partner_id');
        });

        Schema::table('price_lists', function (Blueprint $table) {
            $table->renameColumn('mitra_id', 'partner_id');
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->renameColumn('routed_mitra_id', 'routed_partner_id');
            $table->renameColumn('handler_mitra_id', 'handler_partner_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('mitra_id', 'partner_id');
        });

        // 3. Rename tables back
        Schema::rename('user_mitra_payments', 'user_partner_payments');
        Schema::rename('report_mitra_routings', 'report_partner_routings');
        Schema::rename('mitras', 'partners');

        // 4. Re-add old foreign keys
        Schema::table('reports', function (Blueprint $table) {
            $table->foreign('routed_partner_id')
                ->references('id')
                ->on('partners')
                ->nullOnDelete();
        });

        Schema::table('price_lists', function (Blueprint $table) {
            $table->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();
        });

        Schema::table('chat_threads', function (Blueprint $table) {
            $table->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();
        });

        Schema::table('report_partner_routings', function (Blueprint $table) {
            $table->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();
        });

        Schema::table('user_partner_payments', function (Blueprint $table) {
            $table->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();
        });

        // 5. Update user role values back
        DB::table('users')->where('role', 'mitra')->update(['role' => 'partner']);
    }
};
