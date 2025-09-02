<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use League\Csv\Reader;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class TextCheckerController extends Controller
{
    private const USER_AGENT_DESKTOP = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36";
    private const USER_AGENT_MOBILE = "Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1";

    /**
     * Display the form view.
     */
    public function showForm()
    {
        return view('checker');
    }

    /**
     * Handle the form submission and perform the text check.
     */
    public function check(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            // 1. Parse the CSV file
            $file = $request->file('csv_file');
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset(null); // Assume no header
            $phrases = collect($csv->getRecords())->flatten()->filter()->all();
            
            if (empty($phrases)) {
                return back()->with('error', 'The CSV file is empty or could not be read.');
            }

            // 2. Get other form data
            $url = $request->input('url');
            $deviceType = $request->input('device_type', 'both');
            $isExactMatch = $request->has('exact_match');
            $username = $request->input('username');
            $password = $request->input('password');

            $results = [];
            
            // 3. Perform checks based on device type
            if (in_array($deviceType, ['desktop', 'both'])) {
                $results['desktop'] = $this->performCheck($url, $phrases, self::USER_AGENT_DESKTOP, $isExactMatch, $username, $password);
            }

            if (in_array($deviceType, ['mobile', 'both'])) {
                $results['mobile'] = $this->performCheck($url, $phrases, self::USER_AGENT_MOBILE, $isExactMatch, $username, $password);
            }

            return view('checker', ['results' => $results, 'submitted_url' => $url]);

        } catch (RequestException $e) {
            $status = $e->response ? $e->response->status() : 'N/A';
            if ($status === 401) {
                return back()->with('error', 'Authentication failed (401). Please check credentials.');
            }
            return back()->with('error', "HTTP Error: Server responded with status {$status}.");
        } catch (ConnectionException $e) {
            return back()->with('error', "Connection Error: Could not connect to the host. Please check the URL.");
        } catch (\Exception $e) {
            return back()->with('error', "An unexpected error occurred: " . $e->getMessage());
        }
    }
    
    /**
     * The core logic to fetch a page and check for phrases.
     */
    private function performCheck(string $url, array $phrases, string $userAgent, bool $isExactMatch, ?string $username, ?string $password): array
    {
        $http = Http::timeout(10)->withUserAgent($userAgent);

        if ($username && $password) {
            $http->withBasicAuth($username, $password);
        }
        
        $response = $http->get($url);
        $response->throw(); // Throw an exception for 4xx/5xx responses

        // Use DomCrawler to extract text, similar to BeautifulSoup
        $crawler = new Crawler($response->body());
        $pageText = $isExactMatch ? $crawler->text() : $this->normalizeText($crawler->text());

        $found = [];
        $missing = [];

        foreach ($phrases as $phrase) {
            $phrase = trim($phrase);
            if (empty($phrase)) continue;

            $isFound = false;
            if ($isExactMatch) {
                $isFound = str_contains($pageText, $phrase);
            } else {
                $normalizedPhrase = $this->normalizeText($phrase);
                $pattern = '/(?:^|\s)' . preg_quote($normalizedPhrase, '/') . '(?=\s|$)/';
                $isFound = preg_match($pattern, $pageText);
            }
            
            if ($isFound) {
                $found[] = $phrase;
            } else {
                $missing[] = $phrase;
            }
        }
        
        return ['found' => $found, 'missing' => $missing];
    }
    
    /**
     * Normalizes text for flexible searching.
     */
    private function normalizeText(string $text): string
    {
        $text = str_replace("\xc2\xa0", ' ', $text); // Replace non-breaking spaces
        $text = preg_replace('/\s+/', ' ', $text); // Collapse whitespace
        return trim(strtolower($text));
    }
}