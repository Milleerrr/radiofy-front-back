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
        Schema::table('users', function (Blueprint $table) {
            $table->string('spotify_token');
            $table->string('spotify_refresh_token');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('spotify_token');
            $table->dropColumn('spotify_refresh_token');
            $table->string('email')->change();
            $table->string('password')->change();;
        });
    }
};
