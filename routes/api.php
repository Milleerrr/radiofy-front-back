<?php

use App\Http\Controllers\BBCSoundsController;
use App\Http\Controllers\GifController;
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

    // Route to check if a schedule exists and to retrieve it
    Route::get('/schedule/check-and-retrieve', [BBCSoundsController::class, 'getSchedule'])
        ->name('api.schedule.index');

    // Route to retrieve songs and artists for a specific programme
    Route::get('schedule/programme/details', [BBCSoundsController::class, 'fetchSongsAndArtists']);

    // Route to scrape and save schedule when it does not exist
    // You may want to make this a POST route if you are going to trigger a scraping process
    Route::post('/schedule/scrape-and-save', [BBCSoundsController::class, 'scrapeAndSaveSchedule']);

    // Then create playlists once tracks have been found 
    Route::post('/spotify/add-to-spotify', [SpotifyServiceController::class, 'addToSpotify'])
        ->name('spotify.add-to-spotify');

    Route::post('/spotify/retrieve-song-info', [SpotifyServiceController::class, 'retrieveSongInfo'])
        ->name('spotify.retrieve-song-info');
}); 

Route::get('/random-gif', [GifController::class,'getRandomGif'])
    ->name('radom.gif');
