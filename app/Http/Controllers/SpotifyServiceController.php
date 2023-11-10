<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\SpotifyService;

class SpotifyServiceController extends Controller
{
    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }


    // This endpoint will handle creating a playlist and adding tracks to it.
    public function getSpotifyToken(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Retrieve the access token using the Spotify service
        $accessToken = $this->spotifyService->getSpotifyAccessToken($user);

        // Return the access token in the response
        return response()->json([
            'message' => 'Access token retrieved successfully!',
            'access_token' => $accessToken
        ]);
    }

    public function createPlaylist(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $playlistName = $request->input('name');

        // Call the service method to create a playlist
        $createdPlaylistName = $this->spotifyService->createPlaylist($user, $playlistName);

        return response()->json([
            'message' => 'Playlist created successfully!',
            'playlist_name' => $createdPlaylistName
        ]);
    }

    public function searchTracks(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $request->validate([
            'artist' => 'required|string',
            'trackTitle' => 'required|string',
        ]);

        $artist = $request->input('artist');
        $trackTitle = $request->input('trackTitle');

        $accessToken = $this->spotifyService->getSpotifyAccessToken($user);

        $track = $this->spotifyService->searchTrackOnSpotify($accessToken, $artist, $trackTitle);

        if (!$track) {
            return response()->json(['message' => 'Track not found'], 404);
        }

        // You may want to log the track or do something with it here.
        // For now, we'll just return it.
        return response()->json($track);
    }
}
