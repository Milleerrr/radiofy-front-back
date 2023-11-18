<?php

namespace App\Console\Commands;

use App\Http\Controllers\BBCSoundsController;
use App\Http\Controllers\SpotifyServiceController;
use Illuminate\Http\Request;
use Illuminate\Console\Command;
use Carbon\Carbon;

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

    public function handle()
    {
        $bbcSoundsController = new BBCSoundsController();
        $station = 'radio_one_dance'; // This should be dynamically set as needed
        $endDate = Carbon::today(); // End date is today
        $date = $endDate->copy()->subDays(30); // Start date is 30 days ago

        // Loop through each day from 30 days ago until today
        while ($date->lte($endDate)) {
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

                // Created a  Request instance with this data as 
                // retrieveSongInfo is expecting a request
                $songsRequest = new Request(['scraped_songs' => $songsData]);

                // Call the retrieveSongInfo method with the correct Request object
                $spotifyTracks = $this->spotifyServiceController->retrieveSongInfo($songsRequest);
                echo $spotifyTracks;
            }

            // Increment the date by one day for the next iteration
            $date->addDay();
        }

        $this->info('All programmes from the last 30 days have been scraped.');
    }
}
