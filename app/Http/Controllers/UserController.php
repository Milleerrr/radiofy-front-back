<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // some validation logic
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = $request->password;
        $user->spotify_token = $request->token;
        $user->spotify_refresh_token = $request->refreshToken;
        $user->spotify_id = $request->spotify_id;
        $user->token_expires_at = $request->token_expires_in;
        // then save
        $user->save();

        return $user;
    }
}
