<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_partner_routings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->uuid('partner_id');
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->timestamp('routed_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->cascadeOnDelete();

            $table->foreign('partner_id')
                ->references('id')
                ->on('partners')
                ->cascadeOnDelete();

            $table->index(['partner_id', 'status', 'expires_at']);
            $table->index(['report_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_partner_routings');
    }
};
