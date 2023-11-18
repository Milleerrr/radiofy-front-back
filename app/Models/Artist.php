<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    public function songs() {
        // This will reference the pivot table 'artist_song'
        return $this->belongsToMany(Song::class, 'artist_song');
    }

    public function playlists() {
        // This will reference the pivot table 'artist_playlist'
        return $this->belongsToMany(Playlist::class, 'artist_playlist');
    }    
}
