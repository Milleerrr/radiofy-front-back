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

    public function retrieveSongInfo(Request $request)
    {
        // I need input validation
        $songs = $request->input('songs');
        $accessToken = $this->spotifyService->getSpotifyAccessToken(Auth::user());
        $tracksInfo = [];

        foreach ($songs as $song) {
            $track = $this->spotifyService->searchTrackOnSpotify($accessToken, $song['artist'], $song['trackTitle']);

            if ($track) {
                // If the track is found, add it to the tracks info array
                $tracksInfo[] = $track;
            } else {
                // If a track is not found, you might want to handle it differently.
                // For this example, we'll just add a message indicating failure.
                $tracksInfo[] = ['message' => 'Track not found', 'artist' => $song['artist'], 'trackTitle' => $song['trackTitle']];
            }
        }

        // Return the array of track information
        return response()->json($tracksInfo);
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

    public function addToSpotify(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // You would validate your request here

        $accessToken = $this->spotifyService->getSpotifyAccessToken($user);
        $playlistId = $this->spotifyService->createPlaylist($user, $request->input('playlistName'));

        // Assume 'tracks' is now an array of song objects which include the URIs
        $tracks = $request->input('tracks');

        // Extract URIs from the track objects
        $trackUris = array_column($tracks, 'spotifyUri');

        // Filter out any empty URIs just in case
        $trackUris = array_filter($trackUris);

        if (count($trackUris) > 0) {
            $this->spotifyService->addTracksToPlaylist($accessToken, $playlistId, $trackUris);
        }

        return response()->json(['message' => 'Playlist created and tracks added successfully']);
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

    public function addTracks(Request $request)
    {
        $user = Auth::user();
        $accessToken = $this->spotifyService->getSpotifyAccessToken($user);
        $playlistId = $request->input('playlist_id');
        $trackUris = $request->input('track_uris');

        $result = $this->spotifyService->addTracksToPlaylist($accessToken, $playlistId, $trackUris);

        if ($result) {
            return response()->json(['message' => 'Tracks added successfully']);
        } else {
            return response()->json(['message' => 'An error occurred'], 500);
        }
    }
}
