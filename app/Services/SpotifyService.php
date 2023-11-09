<?php

namespace App\Services;

use App\Models\User;
use GuzzleHttp\Client;

class SpotifyService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    // public function createAndFillPlaylist(User $user, $playlistName, array $tracksInfo)
    // {
    //     $accessToken = $this->getSpotifyAccessToken($user);

    // Create a new Spotify playlist
    // $playlist = $this->createSpotifyPlaylist($accessToken, $user->spotify_id, $playlistName);



    // // Search for each track and collect their Spotify URIs
    // $trackUris = [];
    // foreach ($tracksInfo as $track) {
    //     $searchResult = $this->searchTrackOnSpotify($accessToken, $track['artist'], $track['title']);
    //     if ($searchResult) {
    //         $trackUris[] = $searchResult['uri'];
    //     }
    // }

    // // Add tracks to the newly created Spotify playlist
    // if (!empty($trackUris)) {
    //     $this->addTracksToPlaylist($accessToken, $playlist['id'], $trackUris);
    // }

    // return $playlist;
    // }

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

        return $playlist['name']; // Return the name of the created playlist
    }



    public function searchTrackOnSpotify($accessToken, $artist, $trackTitle)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $query = http_build_query([
            'q' => "artist:\"{$artist}\" track:\"{$trackTitle}\"",
            'type' => 'track',
            'limit' => 1
        ]);

        $response = $this->client->request('GET', "https://api.spotify.com/v1/search?{$query}", [
            'headers' => $headers
        ]);

        $searchResults = json_decode($response->getBody(), true);

        return $searchResults['tracks']['items'][0] ?? null; // Return the first match or null if not found
    }
    private function addTracksToPlaylist($accessToken, $playlistId, array $trackUris)
    {
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
        ];

        $body = json_encode(['uris' => $trackUris]);

        $this->client->request('POST', "https://api.spotify.com/v1/playlists/{$playlistId}/tracks", [
            'headers' => $headers,
            'body' => $body
        ]);
    }
}
