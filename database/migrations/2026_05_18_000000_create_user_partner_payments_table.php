<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_partner_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->uuid('partner_id');
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->string('status')->default('paid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'paid_at']);
            $table->index(['partner_id', 'status']);

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('partner_id')->references('id')->on('partners')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_partner_payments');
    }
};
