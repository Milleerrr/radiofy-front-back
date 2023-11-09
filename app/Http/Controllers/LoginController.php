<?php

namespace App\Http\Controllers;

use App\Models\Login;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render("Auth/Login");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Login $login)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Login $login)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Login $login)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Login $login)
    {
        //
    }

    /**
     * Redirect user to Spotify login
     */
    public function redirectToSpotify()
    {
        // Define the scopes that your application needs.
        // This is needed for the authorsiation to allow my app
        // to perform actions on user's Spotify account
        $scopes = [
            'playlist-modify-public',
            'playlist-modify-private',
            // Add other scopes your application requires
        ];

        // Redirect the user to the Spotify authentication page with the necessary scopes
        return Socialite::driver('spotify')
            ->scopes($scopes) // Pass the scopes array to the scopes method
            ->redirect();
    }


    /**
     * Redirect user to Google login
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function redirectToSpotifyCallback()
    {
        $spotifyUserInfo = Socialite::driver('spotify')->stateless()->user();


        $user = User::updateOrCreate([
            'spotify_id' => $spotifyUserInfo->id,
        ], [
            'name' => $spotifyUserInfo->name,
            'email' => $spotifyUserInfo->email,
            'spotify_token' => $spotifyUserInfo->token,
            'spotify_refresh_token' => $spotifyUserInfo->refreshToken,
            'token_expires_at' => now()->addSeconds($spotifyUserInfo->expiresIn),
        ]);

        Auth::login($user);

        return redirect('/search');
    }
}
