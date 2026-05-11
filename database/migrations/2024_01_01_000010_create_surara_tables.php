<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recreate users table with UUID
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('report_status_logs');
        Schema::dropIfExists('report_routing');
        Schema::dropIfExists('witness_evidences');
        Schema::dropIfExists('witness_reports');
        Schema::dropIfExists('evidences');
        Schema::dropIfExists('trusted_contacts');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('partners');

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user')->after('password');
            }
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('partner_name');
            $table->string('partner_type');
            $table->string('city')->default('Semarang');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('verified')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('report_type')->default('quick_emergency');
            $table->string('category');
            $table->text('description')->nullable();
            $table->float('latitude')->nullable();
            $table->float('longitude')->nullable();
            $table->text('location_text')->nullable();
            $table->boolean('anonymous')->default(false);
            $table->string('status')->default('Submitted');
            $table->uuid('routed_partner_id')->nullable();
            $table->timestamps();
        });

        Schema::create('evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->text('file_url');
            $table->string('file_type')->nullable();
            $table->text('file_hash')->nullable();
            $table->uuid('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
        });

        Schema::create('trusted_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->timestamp('created_at')->nullable();
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

        Schema::create('report_routing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->uuid('partner_id');
            $table->timestamp('routed_at')->nullable();
        });

        Schema::create('report_status_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->uuid('changed_by')->nullable();
            $table->timestamp('changed_at')->nullable();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('action');
            $table->string('target_type')->nullable();
            $table->uuid('target_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('report_status_logs');
        Schema::dropIfExists('report_routing');
        Schema::dropIfExists('witness_evidences');
        Schema::dropIfExists('witness_reports');
        Schema::dropIfExists('evidences');
        Schema::dropIfExists('trusted_contacts');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('partners');
    }
};
