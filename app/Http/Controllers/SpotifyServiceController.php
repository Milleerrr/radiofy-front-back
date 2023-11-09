<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SpotifyService;

class SpotifyController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }


    // This endpoint will handle creating a playlist and adding tracks to it.
    public function addToSpotify(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Validate the request inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'songs' => 'required|array',
            'songs.*.id' => 'required|integer',
            'songs.*.artist' => 'required|string|max:255',
            'songs.*.title' => 'required|string|max:255',
        ]);

        $playlistName = $request->input('playlistName');
        $tracksInfo = $request->input('songs'); // Array of tracks with 'artist' and 'title'

        // Create the playlist and add the tracks in one operation
        $playlist = $this->spotifyService->createAndFillPlaylist($user, $playlistName, $tracksInfo);

        return response()->json([
            'message' => 'Playlist created and songs added successfully!',
            'playlist' => $playlist
        ]);
    }
}
