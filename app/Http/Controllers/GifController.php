<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GifController extends Controller
{
    public function getRandomGif(Request $request)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'http://api.giphy.com/v1/gifs/random', [
            'query' => [
                'api_key' => env('GIPHY_API_KEY'),
                'tag' => 'waiting'
            ]
        ]);

        return response()->json(json_decode($response->getBody(), true));
    }
}
