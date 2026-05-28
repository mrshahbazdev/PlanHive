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
        Schema::table('user_integrations', function (Blueprint $table) {
            $table->string('provider')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_integrations', function (Blueprint $table) {
            // Not easily reversible for enums in all DBs, but this is a fallback.
            $table->string('provider')->change();
        });
    }
};
