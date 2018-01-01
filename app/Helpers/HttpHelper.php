<?php
namespace Mutant\Http\App\Helpers;

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Promise;
use Mutant\String\App\Helpers\StringHelper;

class HttpHelper
{
    /**
     * Removes
     * Make url path match RFC 3986
     * https://tools.ietf.org/html/rfc3986
     *
     * @param $string
     * @return mixed
     */
    public static function sanitizeUrlPath($string)
    {
        $word = str_replace(" ", "", $string); // Get rid of whitespace
        $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.-_~!$&'()*+,;=:@"; // All valid RFC 3986 characters
        $word = StringHelper::removeAllExcept($chars, $word);
        return $word;
    }

    /**
     * Returns array of correct urls.
     * => $this->validateUrlsGood(['asd', "http://test.com"]);
     * => ["http://test.com"]
     * @param array $urls
     * @return array
     */
    public static function validateUrlsGood(array $urls)
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
    public static function validateUrlsBad(array $urls)
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
     * Pass in array of URLs which will be async GET'ed and get back a results array
     * @param array $urls
     * @return mixed
     */
    public static function asyncGet(array $urls)
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