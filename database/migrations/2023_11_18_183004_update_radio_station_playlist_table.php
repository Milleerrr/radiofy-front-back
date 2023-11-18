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
        Schema::table('radio_station_playlists', function (Blueprint $table) {
            $table->dropColumn('spotify_playlist_id');
            $table->string('primary_title');
            $table->string('secondary_title');
            $table->string('image_url');
            $table->string('synopsis');
            $table->string('link');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('radio_station_playlists', function (Blueprint $table) {
            $table->string('spotify_playlist_id');
            $table->dropColumn(['primary_title', 'secondary_title', 'image_url', 'synopsis','link']);
        });

    }
};
