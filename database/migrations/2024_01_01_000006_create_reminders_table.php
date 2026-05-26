<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->morphs('remindable');
            $table->dateTime('remind_at');
            $table->enum('channel', ['in_app', 'email', 'push', 'teams'])->default('in_app');
            $table->boolean('is_sent')->default(false);
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['remind_at', 'is_sent']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
