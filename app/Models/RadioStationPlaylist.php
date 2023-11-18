<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioStationPlaylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'playlist_id',
        'primary_title',
        'secondary_title',
        'image_url',
        'synopsis',
        'link'
    ];
    public function songs() {
        // This will reference the pivot table 'playlist_song'
        return $this->belongsToMany(Song::class, 'playlist_song');
    }

    public function artists() {
        // This will reference the pivot table 'artist_playlist'
        return $this->belongsToMany(Artist::class, 'artist_playlist');
    }
}
