<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_user_routing', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->uuid('target_user_id');
            $table->timestamp('routed_at')->nullable();
            $table->timestamps();

            $table->foreign('report_id')
                ->references('id')
                ->on('reports')
                ->onDelete('cascade');

            $table->foreign('target_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_user_routing');
    }
};

