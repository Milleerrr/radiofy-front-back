<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
            $parsedContent = $this->parseContent($htmlContent); // Make sure this is the correct method name

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

    public function parseContent($htmlContent)
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
}
