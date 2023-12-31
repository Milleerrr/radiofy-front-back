<?php

namespace App\Console\Commands;

use App\Http\Controllers\BBCSoundsController;
use App\Http\Controllers\SpotifyServiceController;
use App\Http\Requests\GetScheduleRequest;
use App\Models\Artist;
use App\Models\RadioStationPlaylist;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ScrapeBBCSounds extends Command
{
    protected $signature = 'scrape:bbcsounds';
    protected $description = 'Scrapes programmes and songs from BBC Sounds';

    protected $spotifyServiceController;
    protected $bbcSoundsController;
    public function __construct()
    {
        parent::__construct();
        // Resolve the SpotifyServiceController out of the service container
        $this->spotifyServiceController = resolve(SpotifyServiceController::class);
        $this->bbcSoundsController = App::make(BBCSoundsController::class);
    }

    //Handle fuction to get all programmes and songs from the past 30 days
    public function handle()
    {
        // Array of radio stations
        $stations = ['radio_one', 'radio_one_dance', '1xtra', 'radio_two', 'radio_three'];
    
        // Iterate over each station
        foreach ($stations as $station) {
            $endDate = Carbon::today(); // End date is today
            $date = $endDate->copy()->subDays(1); // Start date is yesterday
    
            // Loop through each day from yesterday until today
            while ($date->lte($endDate)) {
                $formattedDate = $date->toDateString();
    
                // Create an instance of your custom request class
                $getScheduleRequest = new GetScheduleRequest();
    
                // Simulate filling the request with query parameters
                $getScheduleRequest->merge([
                    'station' => $station,
                    'date' => $formattedDate,
                ]);
    
                // Create a validator with the request's rules
                $validator = app('validator')->make($getScheduleRequest->all(), $getScheduleRequest->rules());
    
                // If validation fails, handle it as required
                if ($validator->fails()) {
                    // Return or handle the validation errors as needed
                    echo "Validation failed for station {$station} on date {$formattedDate}.\n";
                    $this->error('Validation errors: ' . implode(', ', $validator->errors()->all()));
                    continue; // Skip to the next date or station
                }
    
                echo "Validation successful for station {$station} on date {$formattedDate}.\n";
                // Manually set the validator instance on the request
                $getScheduleRequest->setValidator($validator);
    
                // Now the validated method can be safely called within your controller
                $schedule = $this->bbcSoundsController->getSchedule($getScheduleRequest);
                echo 'Schedule' . $schedule;
    
                // Assuming $schedule is an array of programmes
                foreach ($schedule->original['programme_list'] as $programme) {
                    // Simulate a request to get programme tracks
                    $tracksRequest = new Request(['link' => $programme['playlistDetails']['link']]);
                    $tracks = $this->bbcSoundsController->getProgrammeTracks($tracksRequest);
    
                    $songsData = $tracks->original['scraped_songs'];
    
                    // Created a Request instance with this data as 
                    // retrieveSongInfo is expecting a request
                    $songsRequest = new Request(['scraped_songs' => $songsData]);
    
                    // Call the retrieveSongInfo method with the correct Request object
                    $spotifyTracks = $this->spotifyServiceController->retrieveSongInfo($songsRequest);
                    $this->processTracks($programme, $spotifyTracks);
                }
    
                // Increment the date by one day for the next iteration
                $date->addDay();
            }
    
            echo "All programmes for station {$station} have been scraped.\n";
        }
    
        $this->info('All stations have been processed.');
    }    

    protected function processTracks($programme, $spotifyTracks)
    {
        echo "Processing tracks for programme: " . $programme['playlistDetails']['primary_title'] . "\n";
        $playlist = $this->createOrUpdatePlaylist($programme);

        if ($spotifyTracks instanceof JsonResponse) {
            $spotifyTracks = $spotifyTracks->getData(true); // true parameter converts it to an array
        }

        // Here spotifyTracks is the array of track information
        foreach ($spotifyTracks as $trackInfo) {
            // Each $trackInfo should be an individual track's data
            // Make sure $trackInfo is the expected structure and not the entire response
            if (isset($trackInfo['id'], $trackInfo['name'])){
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
        echo "Finished processing tracks for programme.\n";
    }

    protected function createOrUpdatePlaylist($programme)
    {
        echo "Creating or updating playlist for programme: " . $programme['playlistDetails']['primary_title'] . "\n";
        try {
            return RadioStationPlaylist::updateOrCreate(
                [
                    'playlist_id' => $this->extractProgrammeId($programme['playlistDetails']['link']),
                    'primary_title' => $programme['playlistDetails']['primary_title'],
                    'secondary_title' => $programme['playlistDetails']['secondary_title'],
                    'image_url' => $programme['playlistDetails']['image_url'],
                    'synopsis' => $programme['playlistDetails']['synopsis'],
                    'link' => $programme['playlistDetails']['link'],
                ]
            );
        } catch (\Exception $e) {
            Log::error("Failed to create or update playlist: " . $e->getMessage());
            return null;
        }
    }

    protected function extractProgrammeId($link)
    {
        $parts = explode('/', $link);
        return end($parts);
    }

    protected function createOrUpdateSong($spotifyTrack)
    {
        // Check if the album and images keys are set and images array is not empty
        $imageUrl = null;
        if (isset($spotifyTrack['album'], $spotifyTrack['album']['images']) && !empty($spotifyTrack['album']['images'])) {
            $imageUrl = $spotifyTrack['album']['images'][0]['url'] ?? null;
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
