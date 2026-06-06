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
        // Drop unused tables
        Schema::dropIfExists('witness_evidences');
        Schema::dropIfExists('witness_reports');
        Schema::dropIfExists('report_routing');
        Schema::dropIfExists('password_reset_tokens');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('report_routing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->uuid('partner_id');
            $table->timestamp('routed_at')->nullable();
        });

        Schema::create('witness_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->string('witness_name')->nullable();
            $table->string('witness_phone')->nullable();
            $table->text('witness_note')->nullable();
            $table->timestamp('created_at')->nullable();
            
            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
        });

        Schema::create('witness_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('witness_report_id');
            $table->text('file_url');
            $table->string('file_type')->nullable();
            $table->text('file_hash')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            
            $table->foreign('witness_report_id')->references('id')->on('witness_reports')->onDelete('cascade');
        });
    }
};
