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
        Schema::table('playlist_song', function (Blueprint $table) {
            $table->dropColumn('playlist_id');
            $table->unsignedBigInteger('radio_station_playlist_id');
            $table->foreign('radio_station_playlist_id')->references('id')->on('radio_station_playlists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('playlist_song', function (Blueprint $table) {
            $table->dropColumn('radio_station_playlist_id');
            $table->unsignedBigInteger('playlist_id');
            $table->foreign('playlist_id')->references('id')->on('radio_station_playlists')->onDelete('cascade');
        });
    }
};
