<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->enum('source', ['manual', 'business_card', 'outlook', 'import'])->default('manual');
            $table->string('business_card_image')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'last_name', 'first_name']);
        });

        Schema::create('contact_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('tag');
            $table->timestamps();

            $table->unique(['contact_id', 'tag']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_tags');
        Schema::dropIfExists('contacts');
    }
};
