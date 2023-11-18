<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Artist extends Model
{
    use HasFactory;

    protected $fillable = [
        'spotify_artist_id',
        'name',
    ];

    public function songs() {
        // This will reference the pivot table 'artist_song'
        return $this->belongsToMany(Song::class, 'artists_songs');
    }

    public function playlists() {
        // This will reference the pivot table 'artist_playlist'
        return $this->belongsToMany(RadioStationPlaylist::class, 'artist_playlist');
    }    
}
