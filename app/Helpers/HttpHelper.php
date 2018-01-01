<?php
namespace Mutant\Http\App\Helpers;

class HttpHelper
{

    /**
     * Pass in an array of hosts with a single path and
     * @param $url_path
     * @return array
     */
    public function buildUrls($hosts, $url_path)
    {
        // Sanitize the path
        $url_path = $this->sanitizeUrlPath($url_path);

        // Build the array or URLs
        $out = [];
        foreach ($hosts as $host) {
            $out[] = $host . "/{$url_path}";
        }

        // Sanitize the array of Urls
        $out = $this->sanitizeUrls($out);
        return $out;
    }

    /**
     * Make url path match RFC 3986
     * https://tools.ietf.org/html/rfc3986
     *
     * @param $string
     * @return mixed
     */
    public function sanitizeUrlPath($string)
    {
        $word = str_replace(" ", "", $string); // Get rid of whitespace
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.-_~!$&'()*+,;=:@"; // All valid RFC 3986 characters
        $word = $this->removeAllBut($chars, $word);
        return $word;
    }

    /**
     * Returns array of correct urls.
     * => $this->validateUrlsGood(['asd', "http://test.com"]);
     * => ["http://test.com"]
     * @param array $urls
     * @return array
     */
    public function validateUrlsGood(array $urls)
    {
        $out = [];
        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $out[] = $url;
            }
        }
        return $out;
    }

    /**
     * Returns array of incorrect urls.
     * => $this->validateUrlsBad(['asd', "http://test.com"]);
     * => ["asd"]
     * @param array $urls
     * @return array
     */
    public function validateUrlsBad(array $urls)
    {
        $out = [];
        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                $out[] = $url;
            }
        }
        return $out;
    }

    /**
     * Pass in array of URLs and get back a results array
     * @param array $urls
     * @return mixed
     */
    public function getAsyncArray(array $urls)
    {
        // Create a client that doesn't throw on failures
        $client = new Client([
            'http_errors' => false, // No exceptions of 404, 500 etc.
        ]);

        // Build array of promises(note: how the array is built)
        $promises = [];
        foreach ($urls as $key => $url) {
            $url = (string)$url;
            $promises[$url] = $client->getAsync($url);
        }

        // Wait for the requests to complete, even if some of them fail
        $results = Promise\settle($promises)->wait();

        // Return the array of results
        return $results;
    }
}