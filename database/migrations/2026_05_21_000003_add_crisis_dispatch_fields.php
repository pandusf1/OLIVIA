<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('urgency_level')->default('high')->after('status');
            $table->uuid('handler_partner_id')->nullable()->after('routed_partner_id');
            $table->uuid('handler_user_id')->nullable()->after('handler_partner_id');
            $table->timestamp('assigned_at')->nullable()->after('handler_user_id');
            $table->timestamp('location_verified_at')->nullable()->after('assigned_at');
            $table->timestamp('escalated_at')->nullable()->after('location_verified_at');
            $table->timestamp('last_activity_at')->nullable()->after('escalated_at');

            $table->foreign('handler_partner_id')
                ->references('id')
                ->on('partners')
                ->nullOnDelete();

            $table->foreign('handler_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('report_partner_routings', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('routed_at');
            $table->float('distance_km')->nullable()->after('expires_at');
            $table->unsignedSmallInteger('estimated_response_minutes')->nullable()->after('distance_km');
        });

        Schema::create('report_timeline_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->string('event_type');
            $table->text('event_message');
            $table->string('actor_type')->nullable();
            $table->uuid('actor_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->cascadeOnDelete();

            $table->index(['report_id', 'created_at']);
            $table->index('event_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_timeline_events');

        Schema::table('report_partner_routings', function (Blueprint $table) {
            $table->dropColumn(['reviewed_at', 'distance_km', 'estimated_response_minutes']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['handler_partner_id']);
            $table->dropForeign(['handler_user_id']);
            $table->dropColumn([
                'urgency_level',
                'handler_partner_id',
                'handler_user_id',
                'assigned_at',
                'location_verified_at',
                'escalated_at',
                'last_activity_at',
            ]);
        });
    }
};
