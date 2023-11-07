<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

/**
 * Include the default register / login routes.
 */
require __DIR__.'/defaults.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home page route
Route::get('/', [HomeController::class, 'index'])->name('/');

// Search page route
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/login', [LoginController::class, 'index'])->name('login');

// See: https://socialiteproviders.com/Spotify/#installation-basic-usage
// return return Socialite::driver('spotify')->redirect();
// Route::get('/signin', [...])

Route::get('/signin', function () {
    return Socialite::driver('spotify')->redirect();
});

// Needs to be configured as your .env SPOTIFY_REDIRECT_URI
// See: https://socialiteproviders.com/usage/
// Needs to handle 1. New signin (create a new user model), 2. returning user - reference existing model
// See: *** https://laravel.com/docs/10.x/socialite#authentication-and-storage
// Route::get('/signin/spotify', [...])


Route::get('/example1', function () {
    // Render component and pass props
    return Inertia::render('Example1', [
        'initialNames' => [
            'Alice',
            'Bob',
            'Carol'
        ]
    ]);
})->name('example1');

Route::get('/example2', function () {
    return Inertia::render('Example2');
})->name('example2');

