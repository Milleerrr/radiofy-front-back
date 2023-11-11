<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotifyServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {

    /**
     * Returns a random new name
     */
    Route::get('name', function () {
        return fake()->name();
    })->name('api.name')->can('getName', User::class);

}); 

// Then create playlists once tracks have been found 
Route::post('/spotify/add-to-spotify', [SpotifyServiceController::class, 'addToSpotify'])
    ->name('spotify.add-to-spotify');

Route::post('/spotify/retrieve-song-info', [SpotifyServiceController::class, 'retrieveSongInfo'])
    ->name('spotify.retrieve-song-info');
