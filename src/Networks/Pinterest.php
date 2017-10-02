<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use DOMDocument;
use Exception;

/**
 * Extend Latest And Greatest To Support Pinterest
 */
class Pinterest extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--pinterest.json';

    /**
     * @var String
     */
    protected $userName;

    /**
     * Define the API end point
     * @var String
     */
    protected $endpoint = 'http://pinterest.com/';

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
            parent::__construct($options);
            $this->setUserName();
            parent::init();
        } catch (Exception $e) {
            echo '<pre>';
            echo 'Message: ' . $e->getMessage(). PHP_EOL;
            echo 'File: ' . $e->getFile(). PHP_EOL;
            echo 'Line: ' . $e->getLine(). PHP_EOL;
            echo 'Trace:' . PHP_EOL . $e->getTraceAsString(). PHP_EOL;
            echo '</pre>';
        }
    }

    /**
     * Set the page name
     */
    public function setUserName() {
        if (!getenv('PINTEREST_USERNAME')) {
            throw new Exception('No PINTEREST_USERNAME defined in your .env');
        }

        $this->userName = getenv('PINTEREST_USERNAME');
    }

    /**
     * Get the endpoint
     * @return String
     */
    public function getUserEndpoint() {
        return $this->endpoint . $this->userName . DIRECTORY_SEPARATOR;
    }

    /**
     * Get statistics array
     * @return Array
     */
    public function getStatisticsArray() {
        // Get meta data from page
        $endpointResult = @get_meta_tags($this->getUserEndpoint());
        if (!$endpointResult) {
            throw new Exception('No meta data returned from endpoint');
        }

        // Create usable statistics array
        $array = [];
        if ($endpointResult['pinterestapp:followers']) {
            $array['followers'] = $endpointResult['pinterestapp:followers'];
        }
        if ($endpointResult['pinterestapp:following']) {
            $array['following'] = $endpointResult['pinterestapp:following'];
        }
        if ($endpointResult['pinterestapp:pins']) {
            $array['pins'] = $endpointResult['pinterestapp:pins'];
        }

        return $array;
    }

    /**
     * Get posts
     * @return Array
     */
    public function getPostsArray() {
        // Check if required extensions loaded
        if (!extension_loaded('xml')) {
            throw new Exception('Please enable the php xml extension');
        }

        // Check if required extension loaded
        if (!extension_loaded('dom')) {
            throw new Exception('Please enable the php dom extension');
        }

        // Get data from API
        $endpointResult = @file_get_contents($this->getUserEndpoint() . 'feed.rss');
        if (!$endpointResult) {
            throw new Exception('No data returned from endpoint');
        }

        // Convert the xml string into xml
        $feedXml = simplexml_load_string($endpointResult);

        // Convert to array
        $feedObject = json_decode(json_encode($feedXml));

        // Get pins
        $pins = $feedObject->channel->item;

        // Shrink array
        $latestPins = array_slice($pins, 0, $this->maxResults);

        // Create usable array
        $array = [];
        foreach ($latestPins as $pin) {
            // Initalise dom document
            $doc = new DOMDocument();

            // Parse html string from pinterest
            $content = $doc->loadHTML(mb_convert_encoding($pin->description, 'HTML-ENTITIES', 'UTF-8'));

            // Get img element
            $img = $doc->getElementsByTagName('img')[0];

            // Get img dimensions
            $imgDimensions = getimagesize($img->getAttribute('src'));

            // Get description
            $description = $doc->getElementsByTagName('p')[1];

            // Populate array
            $array[] = [
                'title' => $pin->title,
                'date' => strtotime($pin->pubDate),
                'link' => $pin->link,
                'description' => $description->textContent,
                'media' => [
                    'thumbnail' => $img->getAttribute('src'),
                    'width' => $imgDimensions[0],
                    'height' => $imgDimensions[1],
                ]
            ];
        }

        // Remove named keys
        $array = array_values($array);

        return $array;
    }
}
