<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetScheduleRequest;
use Illuminate\Http\Request;
use App\Models\RadioStationPlaylist;
use App\Models\Song;
use App\Models\Artist;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use App\Services\SpotifyService;
use Illuminate\Support\Facades\Log;

class BBCSoundsController extends Controller
{

    protected $spotifyService;

    public function __construct(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }
    public function getSchedule(GetScheduleRequest $request)
    {
        // Validate the inputs
        $validated = $request->validated();
        $station = $validated['station'];
        $date = $validated['date'];

        // Attempt to find all playlist IDs for the provided station and date
        $playlistIds = $this->getRadioStationSchedule($station, $date);

        $programmesInfo = [];

        foreach ($playlistIds as $playlistIdArray) {
            $playlistId = $playlistIdArray['playlist_id'];

            // Find or create the playlist
            $playlist = RadioStationPlaylist::with('songs.artists')
                ->where('playlist_id', $playlistId)
                ->firstOr(function () use ($station, $date, $playlistId) {
                    Log::info('Playlist not found: '. $playlistId);
                    return $this->scrapeAndSaveSchedule($station, $date);
                });

            // Convert the playlist and related models to the appropriate structure for the frontend
            if ($playlist) {
                $programmesInfo[] = $this->formatProgrammes($playlist);
            }
        }

        return response(['programme_list' => $programmesInfo]);
    }

    public function getScheduleWihtoutScrape(GetScheduleRequest $request)
    {
        // Validate the inputs
        $validated = $request->validated();
        $station = $validated['station'];
        $date = $validated['date'];

        // Attempt to find all playlist IDs for the provided station and date
        $playlistIds = $this->getRadioStationSchedule($station, $date);

        $programmesInfo = [];

        foreach ($playlistIds as $playlistIdArray) {
            $playlistId = $playlistIdArray['playlist_id'];

            // Find or create the playlist
            $playlist = RadioStationPlaylist::with('songs.artists')
                ->where('playlist_id', $playlistId)
                ->firstOr(function () use ($playlistId) {
                    Log::info('Playlist not found: '. $playlistId);
                });

            // Convert the playlist and related models to the appropriate structure for the frontend
            if ($playlist) {
                $programmesInfo[] = $this->formatProgrammes($playlist);
            }
        }

        return response(['programme_list' => $programmesInfo]);
    }
    protected function getRadioStationSchedule($station, $date)
    {
        // Build the URL to fetch the schedule
        $url = "https://www.bbc.co.uk/sounds/schedules/bbc_{$station}/{$date}";
        $response = Http::get($url);

        if (!$response->successful()) {
            throw new \Exception('Unable to fetch the schedule from BBC Sounds.');
        }

        $htmlContent = $response->body();
        $programmeData = $this->parseScheduleContent($htmlContent);

        $playlistIds = [];

        foreach ($programmeData as $programme) {
            // Extract the programme ID and store it in the array with a key
            $playlistIds[] = [
                'playlist_id' => $this->extractProgrammeId($programme['link'])
            ];
        }

        return $playlistIds; // Make sure to return the result
    }


    protected function scrapeAndSaveSchedule($station, $date)
    {
        // Build the URL to fetch the schedule
        $url = "https://www.bbc.co.uk/sounds/schedules/bbc_{$station}/{$date}";
        $response = Http::get($url);

        if (!$response->successful()) {
            throw new \Exception('Unable to fetch the schedule from BBC Sounds.');
        }

        $htmlContent = $response->body();
        $programmeData = $this->parseScheduleContent($htmlContent);

        foreach ($programmeData as $programme) {
            $playlistId = $this->extractProgrammeId($programme['link']);

            // Use updateOrCreate to avoid duplicate entries
            $playlist = RadioStationPlaylist::updateOrCreate(
                ['playlist_id' => $playlistId],
                [
                    'primary_title' => $programme['title'],
                    'secondary_title' => $programme['secondaryTitle'],
                    'image_url' => $programme['image'],
                    'synopsis' => $programme['synopsis'],
                    'link' => $programme['link'],
                    // You might want to include other fields like 'updated_at'
                    // 'updated_at' => now(),
                ]
            );

            $tracksRequest = new Request(['link' => $programme['link']]);
            $tracksResponse = $this->getProgrammeTracks($tracksRequest);
            $tracksData = $tracksResponse->getData(true)['scraped_songs'];

            echo "Tracks Data for " . $programme['title'] . ": " . json_encode($tracksData) . "\n";

            foreach ($tracksData as $trackData) {
                $spotifyTracksInfo = $this->spotifyService->searchTrackOnSpotify($trackData['artist'], $trackData['title']);

                // Assuming $spotifyTracksInfo is an array of tracks
                foreach ($spotifyTracksInfo as $trackInfo) {
                    // Make sure to check for the 'id' and 'name' in the track info
   
                    if (isset($trackInfo['id'], $trackInfo['name'], $trackInfo['album']['images'][0]['url'])) {
                            $imageUrl = $trackInfo['album']['images'][0]['url']; // Get the image URL
                

                        // Update or create the song in your database
                        $song = Song::updateOrCreate([
                            'spotify_song_id' => $trackInfo['id'],
                        ], [
                            'title' => $trackInfo['name'],
                            'spotify_uri' => $trackInfo['uri'],
                            'image_url' => $imageUrl,
                            'audio_url' => $trackInfo['preview_url'] ?? null,
                        ]);

                        // Sync the song with the playlist
                        $playlist->songs()->syncWithoutDetaching($song->id);

                        // Iterate through each artist in the track's artist array
                        foreach ($trackInfo['artists'] as $spotifyArtist) {
                            // Update or create the artist in your database
                            $artist = Artist::updateOrCreate([
                                'spotify_artist_id' => $spotifyArtist['id'],
                            ], [
                                'name' => $spotifyArtist['name'],
                            ]);

                            // Sync the artist with the song
                            $song->artists()->syncWithoutDetaching($artist->id);
                        }
                    }
                }
            }
        }

        return $playlist->load('songs.artists');
    }
    // Helper function to format the programmes for JSON response
    protected function formatProgrammes($playlist)
    {
        // Initialize an array to hold the formatted programmes
        $formattedProgrammes = [];

        // Now include the details of the playlist itself
        $formattedProgrammes['playlistDetails'] = [
            'playlist_id' => $playlist->playlist_id,
            'primary_title' => $playlist->primary_title,
            'secondary_title' => $playlist->secondary_title,
            'image_url' => $playlist->image_url,
            'synopsis' => $playlist->synopsis,
            'link' => $playlist->link,
        ];

        // Return the formatted programmes
        return $formattedProgrammes;
    }

    // Helper function to extract programme ID from URL
    protected function extractProgrammeId($link)
    {
        $parts = explode('/', $link);
        return end($parts);
    }

    public function parseScheduleContent($htmlContent)
    {
        // Create a new Crawler instance
        $crawler = new Crawler($htmlContent);

        // Initialize an array to hold the tracks data
        $tracksData = [];

        // Filter the content based on the provided structure and classes
        $crawler->filter('.sc-c-playable-list-card__link')->each(function (Crawler $node) use (&$tracksData) {
            // Extract the link, which is the href attribute of the anchor tag
            $link = $node->attr('href');
            $link = 'https://www.bbc.co.uk' . $link;

            // The title is in a span with the class 'sc-c-metadata__primary'
            $title = $node->filter('.sc-c-metadata__primary')->text();

            // The secondary title or additional info might be in a 'p' with the class 'sc-c-metadata__secondary'
            $secondaryTitle = $node->filter('.sc-c-metadata__secondary')->text();

            // The synopsis or description might be in a 'p' with the class 'sc-c-metadata__synopsis'
            $synopsis = $node->filter('.sc-c-metadata__synopsis')->text();

            $image = $node->filter('img')->attr('src');

            // Add the track details to the tracks data array
            $tracksData[] = [
                'image' => trim($image),
                'title' => trim($title),
                'secondaryTitle' => trim($secondaryTitle),
                'synopsis' => trim($synopsis),
                'link' => $link
            ];
        });

        // Return the extracted tracks data
        return $tracksData;
    }

    function getProgrammeTracks(Request $request)
    {
        // Assuming you are receiving the URL, not the HTML content
        $url = $request->input('link');

        $request->validate([
            'link' => 'required|string',
            // 'playlistId' => 'required|string'
        ]);

        $response = Http::get($url);

        $urlParts = explode('/', $url);
        $programmeId = end($urlParts);

        if ($response->successful()) {
            $htmlContent = $response->body();
            // Parse the HTML content
            $parsedContent = $this->parseProgrammeTracks($htmlContent, $programmeId); // Make sure this is the correct method name

            // Return the parsed content data
            // Use JSON_UNESCAPED_SLASHES to prevent escaping slashes in URLs
            return response()->json([
                'scraped_songs' => $parsedContent
            ], 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            // Handle the error appropriately
            return response()->json([
                'error' => 'Unable to fetch the songs from BBC Sounds.'
            ], 500);
        }
    }

    public function parseProgrammeTracks($htmlContent, $programmeId)
    {
        $crawler = new Crawler($htmlContent);

        // Find the script tag containing the JSON data
        $scriptWithJson = $crawler->filter('script')->reduce(function (Crawler $node) {
            // Look for a pattern that identifies the script with the JSON data
            return strpos($node->text(), 'tracklist') !== false;
        });

        // No matching script tag found
        if ($scriptWithJson->count() === 0) {
            return []; // Or throw an exception
        }

        // Extract the JSON from the script tag
        $jsonText = $scriptWithJson->text();
        // Perform any necessary cleaning of the JSON string here
        // ...

        // Extract JSON data from the script content
        $jsonStart = strpos($jsonText, '{');
        $jsonEnd = strrpos($jsonText, '}') + 1;
        $jsonLength = $jsonEnd - $jsonStart;
        $jsonContent = substr($jsonText, $jsonStart, $jsonLength);

        // Decode the JSON data
        $jsonData = json_decode($jsonContent, true);

        // Check if decoding was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            return []; // Or throw an exception
        }

        // Initialize an array to hold the tracks data
        $tracksData = [];

        // Assuming the structure you provided, loop through the tracks and extract the data
        foreach ($jsonData['tracklist']['tracks'] as $track) {
            $primary = $track['titles']['primary'] ?? '';
            $secondary = $track['titles']['secondary'] ?? '';

            $tracksData[] = [
                'programme_id' => $programmeId,
                'artist' => trim($primary),
                'title' => trim($secondary),
            ];
        }

        return $tracksData;
    }

    public function fetchSongsAndArtists(Request $request)
    {
        // Validate the request parameters
        $validated = $request->validate([
            'playlist_id' => 'required|string',
        ]);

        $playlistId = $validated['playlist_id'];

        // Fetch the playlist with its songs and artists
        $playlist = RadioStationPlaylist::with(['songs.artists'])
            ->where('playlist_id', $playlistId)
            ->firstOrFail();

        // Transform the songs and their artists into a structure suitable for the frontend
        $songsDetails = $playlist->songs->map(function ($song) {
            return [
                'title' => $song->title,
                'spotify_uri' => $song->spotify_uri,
                'image_url' => $song->image_url,
                'audio_url' => $song->audio_url,
                'artists' => $song->artists->pluck('name'), // Just sending artist names
            ];
        });

        return response()->json([
            'songs' => $songsDetails,
        ]);
    }
}
