<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    public function playlists()
    {
        // Assumes your pivot table is named 'playlist_song'
        return $this->belongsToMany(Playlist::class, 'playlist_song');
    }
}
