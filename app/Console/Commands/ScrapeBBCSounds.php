<?php

namespace App\Console\Commands;

use App\Http\Controllers\BBCSoundsController;
use App\Http\Controllers\SpotifyServiceController;
use App\Models\Artist;
use App\Models\RadioStationPlaylist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ScrapeBBCSounds extends Command
{
    protected $signature = 'scrape:bbcsounds';
    protected $description = 'Scrapes programmes and songs from BBC Sounds';

    protected $spotifyServiceController;

    public function __construct()
    {
        parent::__construct();
        // Resolve the SpotifyServiceController out of the service container
        $this->spotifyServiceController = resolve(SpotifyServiceController::class);
    }

    // Handle fuction to get all programmes and songs from the past 30 days
    // public function handle()
    // {
    //     $bbcSoundsController = new BBCSoundsController();
    //     $station = 'radio_one_dance'; // This should be dynamically set as needed
    //     $endDate = Carbon::today(); // End date is today
    //     $date = $endDate->copy()->subDays(30); // Start date is 30 days ago

    //     // Loop through each day from 30 days ago until today
    //     while ($date->lte($endDate)) {
    //         $formattedDate = $date->toDateString();

    //         // Simulate a request to get the schedule for the current date
    //         $request = new Request(['station' => $station, 'date' => $formattedDate]);
    //         $schedule = $bbcSoundsController->getSchedule($request);

    //         // Assuming $schedule is an array of programmes
    //         foreach ($schedule->original['programme_list'] as $programme) {
    //             // Simulate a request to get programme tracks
    //             $tracksRequest = new Request(['link' => $programme['link']]);
    //             $tracks = $bbcSoundsController->getProgrammeTracks($tracksRequest);

    //             $songsData = $tracks->original['scraped_songs']; 

    //             // Created a  Request instance with this data as 
    //             // retrieveSongInfo is expecting a request
    //             $songsRequest = new Request(['scraped_songs' => $songsData]);

    //             // Call the retrieveSongInfo method with the correct Request object
    //             $spotifyTracks = $this->spotifyServiceController->retrieveSongInfo($songsRequest);
    //             $this->processTracks($programme, $spotifyTracks);
    //         }

    //         // Increment the date by one day for the next iteration
    //         $date->addDay();
    //     }

    //     $this->info('All programmes from the last 30 days have been scraped.');
    // }

    // Handle function to get the programmes and songs from the current date
    public function handle()
    {
        $bbcSoundsController = new BBCSoundsController();
        $station = 'radio_one_dance'; // This should be dynamically set as needed
        $date = Carbon::today(); // Only process the current day

        // Format the date to a string
        $formattedDate = $date->toDateString();

        // Simulate a request to get the schedule for the current date
        $request = new Request(['station' => $station, 'date' => $formattedDate]);
        $schedule = $bbcSoundsController->getSchedule($request);

        // Assuming $schedule is an array of programmes
        foreach ($schedule->original['programme_list'] as $programme) {
            // Simulate a request to get programme tracks
            $tracksRequest = new Request(['link' => $programme['link']]);
            $tracks = $bbcSoundsController->getProgrammeTracks($tracksRequest);

            $songsData = $tracks->original['scraped_songs'];

            // Created a Request instance with this data as 
            // retrieveSongInfo is expecting a request
            $songsRequest = new Request(['scraped_songs' => $songsData]);

            // Call the retrieveSongInfo method with the correct Request object
            $spotifyTracksResponse = $this->spotifyServiceController->retrieveSongInfo($songsRequest);
            if ($spotifyTracksResponse instanceof JsonResponse) {
                $spotifyTracks = $spotifyTracksResponse->getData(true); // Convert JSON response to array
                $this->processTracks($programme, $spotifyTracks);
            } else {
                // Handle unexpected response type
                $this->error('Expected a JSON response, got something else.');
                continue;
            }
        }

        $this->info('Programmes for today have been scraped.');
    }


    protected function processTracks($programme, $spotifyTracks)
    {
        $playlist = $this->createOrUpdatePlaylist($programme);
    
        // Here spotifyTracks is the array of track information
        foreach ($spotifyTracks as $trackInfo) {
            // Each $trackInfo should be an individual track's data
            // Make sure $trackInfo is the expected structure and not the entire response
            if (isset($trackInfo['id'], $trackInfo['name'])) {
                $song = $this->createOrUpdateSong($trackInfo);
                $this->associateSongWithPlaylist($playlist, $song);
    
                // Iterate over the artists for the track
                foreach ($trackInfo['artists'] as $spotifyArtist) {
                    $artist = $this->createOrUpdateArtist($spotifyArtist);
                    $this->associateArtistWithSong($song, $artist);
                    $this->associateArtistWithPlaylist($playlist, $artist);
                }
            }
        }
    }

    protected function createOrUpdatePlaylist($programme)
    {
        return RadioStationPlaylist::updateOrCreate(
            [
                'playlist_id' => $this->extractProgrammeId($programme['link']),
                'primary_title' => $programme['title'],
                'secondary_title' => $programme['secondaryTitle'],
                'image_url' => $programme['image'],
                'synopsis' => $programme['synopsis'],
                'link' => $programme['link'],
            ]
        );
    }

    protected function extractProgrammeId($link)
    {
        $parts = explode('/', $link);
        return end($parts);
    }

    protected function createOrUpdateSong($spotifyTrack)
    {
        // First, check if the 'album' key and 'images' key exist and if it has at least one image
        $imageUrl = null;
        if (isset($spotifyTrack['album']['images']) && count($spotifyTrack['album']['images']) > 0) {
            $imageUrl = $spotifyTrack['album']['images'][0]['url'];
        }
    
        return Song::updateOrCreate(
            [
                'spotify_song_id' => $spotifyTrack['id'],
            ],
            [
                'title' => $spotifyTrack['name'],
                'image_url' => $imageUrl,
                'audio_url' => $spotifyTrack['preview_url'] ?? null, // Use null coalescing in case it's not set
                'spotify_uri' => $spotifyTrack['uri'],
            ]
        );
    }    

    protected function associateSongWithPlaylist($playlist, $song)
    {
        $playlist->songs()->syncWithoutDetaching([$song->id]);
    }

    protected function createOrUpdateArtist($spotifyArtist)
    {
        return Artist::updateOrCreate(
            ['spotify_artist_id' => $spotifyArtist['id']],
            [
                'name' => $spotifyArtist['name'],
            ],
        );
    }

    protected function associateArtistWithSong($song, $artist)
    {
        $song->artists()->syncWithoutDetaching([$artist->id]);
    }

    protected function associateArtistWithPlaylist($playlist, $artist)
    {
        $playlist->artists()->syncWithoutDetaching([$artist->id]);
    }
}
