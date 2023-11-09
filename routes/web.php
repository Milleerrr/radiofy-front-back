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

// Login page route
Route::get('/login', [LoginController::class, 'index'])->middleware('guest')->name('login');

// Spotify redirect login
Route::get('/login/spotify', [LoginController::class, 'redirectToSpotify'])
    ->name('spotify');

// Google redirect login
Route::get('/login/google', [LoginController::class, 'redirectToGoogle'])
    ->name('google');

Route::get('/login/spotify/callback', [LoginController::class, 'redirectToSpotifyCallback']);

