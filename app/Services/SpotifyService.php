<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class SpotifyService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function getSpotifyAccessToken(User $user)
    {
        // Check if the current access token is still valid
        if ($user->tokenHasExpired()) {
            // Access token has expired, use the refresh token to get a new one
            $response = $this->client->request('POST', 'https://accounts.spotify.com/api/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $user->spotify_refresh_token,
                    'client_id' => env('SPOTIFY_CLIENT_ID'),
                    'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
                ],
            ]);

            $newToken = json_decode($response->getBody()->getContents(), true);

            // Update the user's access token and expiration time in the database
            $user->update([
                'spotify_token' => $newToken['access_token'],
                'token_expires_at' => now()->addSeconds($newToken['expires_in']),
            ]);

            return $newToken['access_token'];
        }

        // Access token is still valid, return it
        return $user->spotify_token;
    }

    public function createPlaylist(User $user, $playlistName)
    {
        $accessToken = $this->getSpotifyAccessToken($user);

        // Assuming the token has not expired and is correctly retrieved
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $body = json_encode([
            'name' => $playlistName,
            'description' => 'Created via my app', // You can customize this
            'public' => false // Set to true if you want the playlist to be public
        ]);

        // Make the POST request to create a new playlist
        $response = $this->client->request('POST', "https://api.spotify.com/v1/users/{$user->spotify_id}/playlists", [
            'headers' => $headers,
            'body' => $body
        ]);

        $playlist = json_decode($response->getBody(), true);

        return $playlist['id']; // Return the id of the playlist object
    }

    public function searchTrackOnSpotify($accessToken, $artist, $trackTitle)
    {
        return Cache::remember(
            $this->getTrackCacheKey($artist, $trackTitle),  // cache key
            3600,                                            // seconds to remember the key for
            function ()                                     // callback to populate the cache item
            use ($accessToken, $artist, $trackTitle) {
                return $this->searchTrackOnSpotifyUncached($accessToken, $artist, $trackTitle);
            }
        );
    }
    public function searchTrackOnSpotifyUncached($accessToken, $artist, $trackTitle)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $query = http_build_query([
            'q' => "{$artist} {$trackTitle}",
            'type' => 'track',
            'limit' => 1
        ]);

        $response = $this->client->request('GET', "https://api.spotify.com/v1/search?{$query}", [
            'headers' => $headers
        ]);

        $searchResults = json_decode($response->getBody(), true);

        return $searchResults['tracks']['items'][0] ?? null; // Return the first match or null if not found
    }

    private function getTrackCacheKey($artist, $trackTitle)
    {
        return "$artist.$trackTitle";
    }

    public function addTracksToPlaylist($accessToken, $playlistId, $trackUris)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ];

        $body = json_encode(['uris' => $trackUris]);

        $response = $this->client->request('POST', "https://api.spotify.com/v1/playlists/{$playlistId}/tracks", [
            'headers' => $headers,
            'body' => $body,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
