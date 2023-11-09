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

    public function getSpotifyAccessToken(User $user)
    {
        // Check if the current access token is still valid
        if ($user->tokenHasExpired()) {
            // Access token has expired, use the refresh token to get a new one
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://accounts.spotify.com/api/token', [
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
}
