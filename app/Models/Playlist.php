<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    public function songs()
    {
        // Assumes your pivot table is named 'playlist_song'
        return $this->belongsToMany(Song::class, 'playlist_song');
    }

}
