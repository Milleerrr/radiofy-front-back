<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    public function songs() {
        // This will reference the pivot table 'playlist_song'
        return $this->belongsToMany(Song::class, 'playlist_song');
    }

    public function artists() {
        // This will reference the pivot table 'artist_playlist'
        return $this->belongsToMany(Artist::class, 'artist_playlist');
    }
}
