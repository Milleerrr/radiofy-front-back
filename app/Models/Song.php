<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    public function artists() {
        // This will reference the pivot table 'artist_song'
        return $this->belongsToMany(Artist::class, 'artist_song');
    }
    
    public function playlists() {
        // This will reference the pivot table 'playlist_song'
        return $this->belongsToMany(Playlist::class, 'playlist_song');
    }
}
