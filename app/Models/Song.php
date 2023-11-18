<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'spotify_song_id',
        'title',
        'image_url',
        'audio_url',
        'spotify_uri',
    ];

    public function artists() {
        // This will reference the pivot table 'artist_song'
        return $this->belongsToMany(Artist::class, 'artists_songs');
    }
    
    public function playlists() {
        // This will reference the pivot table 'playlist_song'
        return $this->belongsToMany(RadioStationPlaylist::class, 'playlist_song');
    }
}
