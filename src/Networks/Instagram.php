<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Exception;

/**
 * Extend Latest And Greatest To Support Instagram
 */
class Instagram extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--instagram.json';

    /**
     * Initalise the API secret variable
     * @var String
     */
    protected $pageName;

    /**
     * Define the API end point
     * @var String
     */
    protected $endpoint = 'https://www.instagram.com/';

    /**
     * Prepare enpoint response variable
     * @var Object
     */
    protected $endpointResponse;

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
        if (!getenv('INSTAGRAM_USERNAME')) {
            throw new Exception('No INSTAGRAM_USERNAME defined in your .env');
        }

        $this->userName = getenv('INSTAGRAM_USERNAME');
    }

    /**
     * Get the Instagram API endpoint
     * @return String
     */
    public function getPageDataApiEndpoint() {
        $args = [
            '__a' => 1
        ];

        return $this->endpoint . $this->userName . '/?' . urldecode(http_build_query($args));
    }

    /**
     * Get endpoint response
     * @return Object
     */
    public function getEndpointResponse() {
        if (!$this->endpointResponse) {
            // Get data from API
            $endpointResult = @file_get_contents($this->getPageDataApiEndpoint());
            if (!$endpointResult) {
                throw new Exception('No data returned from endpoint');
            }

            // Convert to object
            $this->endpointResponse = json_decode($endpointResult);
        }

        return $this->endpointResponse;
    }

    /**
     * Get releavant statistics
     * @return Array
     */
    public function getStatisticsArray() {
        // Get api data
        $data = $this->getEndpointResponse();

        // Create statistics array
        $statistics = [
            'followers' => $data->user->followed_by->count,
            'following' => $data->user->follows->count
        ];

        return $statistics;
    }

    /**
     * Get an array of posts
     * @return Array
     */
    public function getPostsArray() {
        // Get api data
        $data = $this->getEndpointResponse();

        // Create posts array
        $posts = [];
        $index = 0;
        foreach ($data->user->media->nodes as $post) {
            $posts[] = [
                'id' => $post->id,
                'text' => $post->caption,
                'date' => $post->date,
                'likes' => $post->likes->count,
                'media' => [
                    'thumbnail' => $post->display_src,
                    'width' => $post->dimensions->width,
                    'height' => $post->dimensions->height
                ]
            ];

            $index ++;
            if ($index === $this->maxResults) {
                break;
            }
        }

        return $posts;
    }
}
