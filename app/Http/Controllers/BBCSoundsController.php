<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BBCSoundsController extends Controller
{
    public function getSchedule(Request $request)
    {
        $station = $request->input('station');
        $date = $request->input('date');

        // Validate the inputs
        $request->validate([
            'station' => 'required|string',
            'date' => 'required|date',
        ]);

        // Format the date to match the required format by the BBC website
        // (Assuming you have some date formatting logic here)

        // Build the URL to fetch the schedule
        $url = "https://www.bbc.co.uk/sounds/schedules/bbc_{$station}/{$date}";

        // Fetch the HTML content
        $response = Http::get($url);
        if ($response->successful()) {
            $htmlContent = $response->body();

            // Parse the HTML content
            $parsedContent = $this->parseScheduleContent($htmlContent); // Make sure this is the correct method name

            // Return the parsed content data
            // Use JSON_UNESCAPED_SLASHES to prevent escaping slashes in URLs
            return response()->json([
                'programme_list' => $parsedContent
            ], 200, [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } else {
            // Handle the error appropriately
            return response()->json([
                'error' => 'Unable to fetch the schedule from BBC Sounds.'
            ], 500);
        }
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

            // Add the track details to the tracks data array
            $tracksData[] = [
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
        ]);

        $response = Http::get($url);

        return $response;

        if ($response->successful()) {
            $htmlContent = $response->body();
            // Parse the HTML content
            $parsedContent = $this->parseProgrammeTracks($htmlContent); // Make sure this is the correct method name

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

    public function parseProgrammeTracks($htmlContent)
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
                'artist' => trim($primary),
                'title' => trim($secondary),
            ];
        }

        return $tracksData;
    }
}
