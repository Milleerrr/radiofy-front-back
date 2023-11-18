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
        Schema::rename('playlists', 'radio_station_playlist');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('radio_station_playlist', 'playlists');
    }
};
