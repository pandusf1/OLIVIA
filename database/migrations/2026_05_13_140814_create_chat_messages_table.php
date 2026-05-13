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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('chat_thread_id');
            $table->string('sender_type'); // 'user' atau 'partner'
            $table->uuid('sender_id');
            $table->text('message');
            $table->timestamps();

            $table->foreign('chat_thread_id')
                ->references('id')
                ->on('chat_threads')
                ->cascadeOnDelete();

            $table->index(['chat_thread_id','created_at']);

            $table->unique(['chat_thread_id','sender_type','sender_id','message','created_at']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
